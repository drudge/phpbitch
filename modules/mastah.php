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
 
class Net_SmartIRC_module_mastah
{
    var $name = 'mastah';
    var $description = 'this module will return if the bot is the current mastah or not.';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    var $actionid;
    
    function module_init(&$irc)
    {
        $this->actionid = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!mastah', $this, 'mastah');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid);
    }
    
    function mastah(&$irc, &$data)
    {
        global $bot;
    
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            if ($bot->isMastah($irc, $data)) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am mastah!');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am not mastah');
            }
        }
    }
}
?>