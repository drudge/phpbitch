<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2003 Mirco 'meebey' Bauer <meebey@php.net>
 *
 * Full GPL License: <http://www.gnu.org/licenses/gpl.txt>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */
 
class Net_SmartIRC_module_cvscheckout
{
    var $name = 'cvscheckout';
    var $version = '$Revision$';
    var $description = 'this module allows to do a realtime CVS rebuilt of the bot with restarting the bot.';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!cvs-checkout', $this, 'cvscheckout');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function cvscheckout(&$irc, &$data)
    {
        global $bot;
        
        if ($data->channel === null) {
            $data->channel = '#linux-help';
        }
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message($data->type, $data->nick, 'CVS checkout starting...', SMARTIRC_CRITICAL);
            
            $homedir = exec('echo ~');
            $file = file($homedir.'/.cvspass');
            if (!in_array('anonymous@cvs.meebey.net', $file)) {
                $fp = fopen('~/.cvspass', 'a');
                fwrite($fp, "/1 :pserver:anonymous@cvs.meebey.net:2401/cvs A\n");
                fclose($fp);
            }
            
            $output = array();
            exec('cd ~; cvs -d :pserver:anonymous@cvs.meebey.net:/cvs checkout phpbitch', $output);
            foreach ($output as $line) {
                $irc->message($data->type, $data->nick, $line, SMARTIRC_CRITICAL);
            }
            $irc->message($data->type, $data->nick, 'CVS checkout done.', SMARTIRC_CRITICAL);
            $irc->message($data->type, $data->nick, 'restarting...', SMARTIRC_CRITICAL);
            $irc->quit('CVS rebuilt, restarting...', SMARTIRC_CRITICAL);
            
            // suicide in 5 seconds (time enough to send all CVS output)
            $irc->registerTimehandler(5000, $this, 'kill');
        } else {
            $irc->message($data->type, $data->nick, 'you are not authorized to do this!');
        }
    }
    
    function kill(&$irc, &$data)
    {
        // good bye
        exit;
    }
}
?>