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
 
class Net_SmartIRC_module_log
{
    var $name = 'log';
    var $description = 'this module shows the bot log, for debugging reasons.';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!log', $this, 'log');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function log(&$irc, &$data)
    {
        if (!isset($data->messageex[1])) {
            $irc->message($data->type, $data->nick, 'wrong parameter count:');
            $irc->message($data->type, $data->nick, 'usage: !log $lines');
        }
        
        $howmuchlines = $data->messageex[1];
        $file = file('phpbitch.log');
        $numlines = count($file);
        
        for($i = $numlines - $howmuchlines; $i < $numlines; $i++) {
            $irc->message($data->type, $data->nick, '['.$i.'] '.$file[$i]);
        }
    }
}
?>