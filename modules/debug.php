<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2004 Mirco 'meebey' Bauer <meebey@php.net>
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
 
class Net_SmartIRC_module_debug
{
    var $name = 'debug';
    var $version = '$Revision$';
    var $description = 'this module shows debugging info';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!dump_user', $this, 'dump_user');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!dump_channel', $this, 'dump_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!execute_eval', $this, 'execute_eval');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function dump_user(&$irc, &$data)
    {
        if ($data->type != SMARTIRC_TYPE_CHANNEL) {
            $target = $data->nick;
        } else {
            $target = $data->channel;
        }

        $nick = $data->messageex[1];
        $channel = $data->messageex[2];
        $user = $irc->getUser($channel, $nick);
        
        $dump = var_export($user, true);
        $print = explode("\n", $dump);
        $irc->message($data->type, $target, 'dumping user: '.$nick.' on channel: '.$channel);
        $irc->message($data->type, $target, $print);
    }
    
    function dump_channel(&$irc, &$data)
    {
        if ($data->type != SMARTIRC_TYPE_CHANNEL) {
            $target = $data->nick;
        } else {
            $target = $data->channel;
        }

        $channelname = $data->messageex[1];
        $channel = $irc->getChannel($channelname);
        
        $dump = var_export($channel, true);
        $print = explode("\n", $dump);
        $irc->message($data->type, $target, 'dumping channel: '.$channel);
        $irc->message($data->type, $target, $print);
    }
    
    function execute_eval(&$irc, &$data)
    {
        if ($data->type != SMARTIRC_TYPE_CHANNEL) {
            $target = $data->nick;
        } else {
            $target = $data->channel;
        }

        $php_codear = array_slice($data->messageex, 1);
        $php_code = implode(" ", $php_codear);
        ob_start();
        eval($php_code);
        $output = ob_get_contents();
        ob_end_clean();
        
        $print = explode("\n", $output);
        $irc->message($data->type, $target, 'executed code: '.$php_code);
        $irc->message($data->type, $target, $print);
    }
}
?>