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
    var $version = '$Revision$';
    var $description = 'this module has commands to add/remove/search bots user database.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>, Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!who_all ', $this, 'who');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!who ', $this, 'who');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!adduser ', $this, 'adduser');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!addlevel ', $this, 'addlevel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!deluser ', $this, 'deluser');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!dellevel ', $this, 'dellevel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!level_all ', $this, 'level');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!level ', $this, 'level');
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
        
        // when the !who_all command was used, all bots reply
        if(!$bot->isMastah($irc, $data) &&
           $data->messageex[0] != '!who_all') {
            // we are not mastah and the command was not who_all, so lets return
            return;
        }
        
        if ($irc->isJoined($data->channel, $lookupfor)) {
            $user =& $irc->getUser($data->channel, $lookupfor);
            $host = $user->host;
            $ident = $user->ident;
        } else {
            $irc->message($data->type, $data->channel, $requester.': '.$lookupfor.' is not in this channel');
            return;
        }
        
        $newdata->host = $host;
        $newdata->nick = $lookupfor;
        $newdata->ident = $ident;
        $newdata->channel = $data->channel;
        $result = $bot->reverseverify($irc, $newdata);
        
        if ($result !== false) {
            $irc->message($data->type, $data->channel, $requester.': '.$lookupfor.' is '.$result.'['.gethostbyname($host).']');
        } else {
            $irc->message($data->type, $data->channel, $lookupfor.' is not a registered user of '.$data->channel.'.');
        }
    }
        
    function adduser(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        $nick = $data->messageex[1];
        $ident = $data->messageex[2];
        $host = $data->messageex[3];
        $level = $data->messageex[4];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $query = "INSERT INTO users (nickname,ident,host) VALUES('".$nick."','".$ident."','".$host."')";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message($data->type, $data->channel, 'Error adding: '.$error);
            } else {
                $irc->message($data->type, $data->channel, 'Added user '.$nick.' ident: '.$ident.' host: '.$host);
            }
        }
    }
    
    function addlevel(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        $nick = $data->messageex[1];
        $channel = $data->messageex[2];
        $level = $data->messageex[3];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $query = "INSERT INTO users_levels (user,channel,level) VALUES('".$nick."','".$channel."','".$level."')";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message($data->type, $data->channel, 'Error adding: '.$error);
            } else {
                $irc->message($data->type, $data->channel, 'Added level for '.$nick.' channel: '.$channel.' level: '.$level);
            }
        }
    }
    
    function deluser(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        $nick = $data->messageex[1];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $query = "DELETE FROM users WHERE nickname='".$nick."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message($data->type, $data->channel, 'Error removing: '.$error);
            } else {
                $irc->message($data->type, $data->channel, 'Deleted user '.$nick.' from registered users database.');
            }
            
            $query = "DELETE FROM users_levels WHERE user='".$nick."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message($data->type, $data->channel, 'Error removing: '.$error);
            } else {
                $irc->message($data->type, $data->channel, 'Deleted levels for '.$nick.' from levels database.');
            }
        }
    }
    
    function dellevel(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        $nick = $data->messageex[1];
        $channel = $data->messageex[2];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $query = "DELETE FROM users_levels WHERE user='".$nick."' AND channel='".$channel."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message($data->type, $data->channel, 'Error removing: '.$error);
            } else {
                $irc->message($data->type, $data->channel, 'Deleted level for '.$nick.' channel: '.$channel.' from levels database.');
            }
        }
    }
    
    function level(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];
        
        if(!$bot->isMastah($irc, $data) &&
           $data->messageex[0] != '!level_all') {
            return;
        }
        
        if ($irc->isJoined($data->channel, $nick)) {
            $victim = &$irc->getUser($data->channel, $nick);
        } else {
            $irc->message($data->type, $data->channel, $nick.' is not in '.$data->channel.'!');
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        $level = $bot->getLevel($newresult, $data->channel);
        if ($newresult !== false) {
            $irc->message($data->type, $data->channel, $nick.' has a level of ['.$level.'].');
        } else {
            $irc->message($data->type, $data->channel, $nick.' has no level!');
        }
    }
}
?>