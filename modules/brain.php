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
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        global $config;
        
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!search', $this, 'search_db');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!learn', $this, 'learn');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!forget', $this, 'forget');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tell', $this, 'tell');
        
        if ($config['answer_questions']) {
            $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*\?$', $this, 'answerQuestion');
        }
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================
    function search_db(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        if (!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $search = $data->messageex[1];
        $lowersearch = strtolower($data->messageex[1]);
        
        if (!empty($search)) {
            $result = $mdb->query("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $numrows = $mdb->numRows($result);
            if ($numrows > 0) {
                $row = $mdb->fetchRow($result);
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
        global $bot;
        global $mdb;
        $usersquery = $data->messageex[1];
        
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
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $query = "INSERT INTO brain( `query`,`response`,`count`) VALUES('".$usersquery."','".$response."','0')";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.$error);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$usersquery.' as '.$response);
            }
        }
    }
    //===============================================================================================
    function forget(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        $usersquery = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $query = "DELETE FROM brain WHERE query='".$usersquery."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                $error = mdbError($result);
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.$error);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I will now plead to the 5th about '.$usersquery);
            }
        }
    }
    //===============================================================================================
    function tell(&$irc, &$data)
    {
        global $bot;
        global $mdb;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $n00b = $data->messageex[1];
        $search = $data->messageex[3];
        $lowersearch = strtolower($data->messageex[3]);
        
        if (!empty($search)) {
            $result = $mdb->query("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $numrows = $mdb->numRows($result);
            if ($numrows > 0) {
                $row = $mdb->fetchRow($result);
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
        global $mdb;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $word = $data->messageex[count($data->messageex)-1];
        $search = substr($word,0,strlen($word)-1);
        $lowersearch = strtolower($search);
        
        if (!empty($search)) {
            $result = $mdb->query("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $numrows = $mdb->numRows($result);
            if ($numrows > 0) {
                $row = $mdb->fetchRow($result);
                $response = $row[0];
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$response);
            } 
        } 
    }
}
?>