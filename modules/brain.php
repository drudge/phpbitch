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
 
class Net_SmartIRC_module_brain
{
    var $name = 'brain';
    var $description = 'this module allows for adding, deleting, and searching from bots "brain".';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid1;
    var $actionid2;
    var $actionid3;
    var $actionid4;
    var $actionid5;
    
    function module_init(&$irc)
    {
        global $config;
        
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!search', $this, 'search_db');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!learn', $this, 'learn');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!forget', $this, 'forget');
        $this->actionid4 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tell', $this, 'tell');
        $this->actionid5 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*\?$', $this, 'answerQuestion');
    }
    
    function module_exit(&$irc)
    {
        global $config;
        
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
        $irc->unregisterActionid($this->actionid3);
        $irc->unregisterActionid($this->actionid4);
        $irc->unregisterActionid($this->actionid5);
    }
    
    //===============================================================================================
    function search_db(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        global $config;
        $requester = $data->nick;
        $search = $data->messageex[1];
        $lowersearch = strtolower($data->messageex[1]);
        
        if (!empty($search)) {
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $bot->dbquery("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            $result = $bot->dbquery($query);
            $numrows = mysql_num_rows($result);
            if ($numrows > 0) {
                $row = mysql_fetch_array($result);
                $response = $row[0];
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': ['.$search.'] '.$response);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I know nothing about '.$search);
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': Please enter something to search for.');
        }
    }
    //===============================================================================================
    function learn(&$irc, &$data)
    {
        global $config;
        global $bot;
        $usersquery = $data->messageex[1];
        //$response = $data->messageex[2];
        
        // Get the response
        $temp=explode(' ',$data->message);
        $response='';
        for ($i=2;$i<count($temp);$i++) {
            $response.=' '.$temp[$i];
        }
        
        $response=trim($response);
         
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data);
            
            if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
                $query = "INSERT INTO brain( `query`,`response`,`count`) VALUES('".$usersquery."','".$response."','0')";
                $res = $bot->dbquery($query);
                
                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$usersquery.' as '.$response);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================
    function forget(&$irc, &$data)
    {
        global $bot;
        $usersquery = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ( $result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $query = "DELETE FROM brain WHERE query='".$usersquery."'";
            $res = $bot->dbquery($query);
            
            if ($res) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I will now plead to the 5th about '.$usersquery);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
            }
        }
    }
    //===============================================================================================
    function tell(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $n00b = $data->messageex[1];
        $search = $data->messageex[3];
        $lowersearch = strtolower($data->messageex[3]);
        
        if (!empty($search)) {
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $bot->dbquery("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            $result = $bot->dbquery($query);
            $numrows = mysql_num_rows($result);
            if ($numrows > 0) {
                $row = mysql_fetch_array($result);
                $response = $row[0];
                $irc->message(SMARTIRC_TYPE_QUERY, $n00b, '['.$search.'] '.$response);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I know nothing about '.$search.', therefore '.$n00b.' won\'t either.');
            }
        }
    }
    //===============================================================================================
    function answerQuestion(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        global $config;
        
        $requester = $data->nick;
        $crap=explode(' ',$data->message);
        $word=$crap[count($crap)-1];
        $search=substr($word,0,strlen($word)-1);
        $lowersearch = strtolower($search);
        
        if (!empty($search)) {
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $bot->dbquery("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            $result = $bot->dbquery($query);
            $numrows = mysql_num_rows($result);
            if ($numrows > 0) {
                $row = mysql_fetch_array($result);
                $response = $row[0];
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$response);
            } 
        } 
    }
}
?>