<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2003 Nicholas 'DrUDgE' Penree <drudge@x-php.net>
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
 
class Net_SmartIRC_module_ident
{
    var $name = 'ident';
    var $version = '$Revision$';
    var $description = 'Gain access to the bots from different host than what is registered.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!ident', $this, 'login');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================
    function login(&$irc, &$data)
    {
        global $config;
        global $bot;
        
        if (count($data->messageex) < 5) {
            $irc->message($data->type, $data->nick, 'usage: !ident <user> <skey> <level> <#channel>');
            return;
        }
        
        $nick = $data->messageex[1];
        $pass = md5($data->messageex[2]);
        $level = $data->messageex[3];
        $channel = $data->messageex[4];
        
        $irc->message($data->type, $data->nick, 'Entering identify mode...');
        
        if ($pass == $config['emergency_users'][strtolower($nick)] && ($level <= $bot->getLevel($nick, $channel))) {
            $irc->message($data->type, $data->nick, 'Authed on '.$channel.' as '.$nick.' with a level of '.$level);
            switch ($level) {
                case USER_LEVEL_NORMAL:
                case USER_LEVEL_FRIEND:
                    break;
                case USER_LEVEL_VOICE:
                    $irc->voice($channel, $data->nick);
                    break;
                case USER_LEVEL_OPERATOR:
                    $irc->op($channel, $data->nick);
                    break;
                case USER_LEVEL_MASTER:
                case USER_LEVEL_BOT:
                    $irc->mode($channel, '+ov '.$data->nick.' '.$data->nick);
                    break;
            }
        } else {
            $irc->message($data->type, $data->nick, 'Alert: Wrong infomation passed, logging.');
        }
    }
}
?>