<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2003 Nicholas 'DrUDgE' Penree <drudge@x-php.net>
 *                    Mirco 'meebey' Bauer <meebey@php.net>
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
 
class Net_SmartIRC_module_users
{
    var $name = 'users';
    var $description = 'this module has commands to add/remove/search bots user database.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>, Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!who_all ', $this, 'who');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!who ', $this, 'who');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!adduser', $this, 'adduser');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deluser', $this, 'deluser');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!level', $this, 'level');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    //===============================================================================================    
    function who(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $lookupfor = $data->messageex[1];
        $lowerlookupfor = strtolower($data->messageex[1]);
        
        // when the !who_all command was used, all bots reply
        if(!$bot->isMastah($irc, $data) &&
           !$data->messageex[0] == "!who_all") {
            return;
        }
        
        if (isset($irc->channel[$data->channel]->users[$lowerlookupfor])) {
            $host = $irc->channel[$data->channel]->users[$lowerlookupfor]->host;
            $ident = $irc->channel[$data->channel]->users[$lowerlookupfor]->ident;
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is not in this channel');
            return;
        }
        
        $newdata->host = $host;
        $newdata->nick = $lookupfor;
        $newdata->ident = $ident;
        $newdata->channel = $data->channel;
        $result = $bot->reverseverify($irc, $newdata);
        
        if ($result !== false) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is '.$result.'['.gethostbyname($host).']');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $lookupfor.' is not a registered user of '.$data->channel.'.');
        }
    }
        
    //===============================================================================================
    function adduser(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];
        $ident = $data->messageex[2];
        $host = $data->messageex[3];
        $level = $data->messageex[4];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $query = "INSERT INTO users (nickname,ident,host,level) VALUES('".$nick."','".$ident."','".$host."','".$level."')";
            $res = $bot->dbquery($query);
            
            if ($res) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$nick.' ident: '.$ident.' host: '.$host.' with a level of '.$level);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
            }
        }
    }
    //===============================================================================================
    function deluser(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $query = "DELETE FROM users WHERE nickname='".$nick."'";
            $res = $bot->dbquery($query);
            
            if ($res) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Deleted '.$nick.' from registered users database.');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
            }
        }
    }
    //===============================================================================================
    function level(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];
        
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        if (isset($irc->channel[strtolower($data->channel)]->users[strtolower($nick)])) {
            $victim = &$irc->channel[strtolower($data->channel)]->users[strtolower($nick)];
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $nick.' is not in '.$data->channel.'!');
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        $level = $bot->get_level($newresult);
        if ($newresult !== false) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $nick.' has a level of ['.$level.'].');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $nick.' has no level!');
        }
    }
}
?>
