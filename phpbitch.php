#!/usr/local/bin/php
<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * phpbitch - an IRC bot written in PHP which is based on SmartIRC class
 *
 * Copyright (c) 2003 Mirco 'meebey' Bauer <mail@meebey.net>
 *                    Nicholas 'DrUDgE' Penree <drudge@x-php.net>
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

// Define User Modes
define('USER_LEVEL_NORMAL',    0);
define('USER_LEVEL_FRIEND',    1);
define('USER_LEVEL_VOICE',     2);
define('USER_LEVEL_OPERATOR',  3);
define('USER_LEVEL_MASTER',    4);
define('USER_LEVEL_BOT',       5);

// Include dependent files
include_once('config.php');
include_once('Net/SmartIRC.php');

// Initialize start time
$start=0;

//===============================================================================================
class PHPBitch
{
    function dbquery($query)
    {
        global $mysql_db;
        global $mysql_server;
        global $mysql_username;
        global $mysql_password;
        global $mysql_link;
        global $irc;
        
        if (!is_resource($mysql_link)) {
            $mysql_link = mysql_connect($mysql_server, $mysql_username, $mysql_password);
        }
        
        $result = mysql_db_query($mysql_db, $query, $mysql_link);
        return $result;
    }
    //===============================================================================================
    function get_level($nick)
    {
        $query = "SELECT level FROM dnsentries WHERE nickname='".$nick."'";
        $result = $this->dbquery($query);
        $numrows = mysql_num_rows($result);
        
        if ($numrows > 0) {
            $row = mysql_fetch_array($result);
            return $row[0];
        }
        return 0;
    }
    //===============================================================================================
    function reverseverify(&$irc, &$data)
    {
        $query = "SELECT nickname,ident,dnsalias FROM dnsentries";
        $result = $this->dbquery($query);
        
        $list = array();
        $foundnick = false;
        $userip = gethostbyname($data->host);
        while ($row = mysql_fetch_array($result)) {
            $dbip = gethostbyname($row['dnsalias']);
            $dbnickname = $row['nickname'];
            $dbident = $row['ident'];
            
            if ($userip == $dbip && $data->ident == $dbident) {
                if ($dbnickname != $data->nick) {
                    $foundnick = $dbnickname;
                }
                break;
            }
        }
        
        if ($foundnick !== false) {
            $result = $this->verify($irc, $data, $foundnick);
        } else {
            $result = $this->verify($irc, $data);
        }
        
        if ($result !== false) {
            return $result;
        } else {
            return false;
        }
    }
    //===============================================================================================
    function verify(&$irc, &$data, $ircnickname = null)
    {
        global $config;
        $who = $data->nick;
        $loweredwho = strtolower($who);
        $channel = $data->channel;
        $ident = $data->ident;
        
        if ($ircnickname !== null) {
            $dbwho = $data->nick;
            $who = $ircnickname;
            $loweredwho = strtolower($who);
        } else {
            $dbwho = $who;
        }

        if (isset($irc->channel[$channel]->users[$loweredwho])) {
            $query = "SELECT nickname,ident,dnsalias FROM dnsentries WHERE nickname='".$dbwho."'";
            $result = $this->dbquery($query);
            $numrows = mysql_num_rows($result);
            
            if ($numrows > 0) {
                $host = $irc->channel[$channel]->users[$loweredwho]->host;
                $ip = gethostbyname($host);
                
                $found = false;
                while ($row = mysql_fetch_array($result)) {
                    $dbident = $row['ident'];
                    $dnsaliasip = gethostbyname($row['dnsalias']);
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "who: $who dbwho: $dbwho ident: $ident dbident: $dbident ip: $ip dbip: $dnsaliasip");
                    
                    if ($dnsaliasip == $ip && $dbident == $ident) {
                        return $row['nickname'];
                    }
                }
            }
        }
        
        return false;
    }
    //===============================================================================================
    
    function isMastah(&$irc, &$data)
    {
        if ($data->type == SMARTIRC_TYPE_QUERY ||
            $data->type == SMARTIRC_TYPE_NOTICE) {
            // on private messages we always reply
            return true;
        }
        
        global $config;
        
        $candidates = array();
        foreach ($config['friend_bots'] as $key => $value) {
            $bot = $value;
            if (isset($irc->channel[$data->channel]->users[strtolower($bot)])) {
                $user = &$irc->channel[$data->channel]->users[strtolower($bot)];
                
                $newdata->host = $user->host;
                $newdata->nick = $user->nick;
                $newdata->ident = $user->ident;
                $newdata->channel = $data->channel;
                $result = $this->reverseverify($irc, $newdata);
                
                if ($result !== false &&
                    $this->get_level($user->nick) == USER_LEVEL_BOT) {
                    $candidates[] = $user->nick;
                }
            }
        }
        
        if (isset($candidates[0]) && $candidates[0] == $irc->_nick) {
            // ok, it's showtime! we are the mastah!!!
            return true;
        } else {
            return false;
        }
    }
}

$bot = &new PHPBitch();
$irc = &new Net_SmartIRC();
$irc->setLogdestination(SMARTIRC_FILE);
$irc->setLogfile('./phpbitch.log');
$irc->setModulepath($config['modules_path']);
$irc->setChannelSynching(true);
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setUseSockets(true);
$irc->setAutoRetry(true);
$irc->setAutoReconnect(true);
$irc->setReceiveTimeout(600);
$irc->setTransmitTimeout(600);
$irc->setSenddelay(500);

// Functionality
$irc->loadModule('google');
$irc->loadModule('chancmds');
$irc->loadModule('botcmds');
$irc->loadModule('users');
$irc->loadModule('brain');
$irc->loadModule('temp');
//$irc->loadModule('dictionary');
$irc->loadModule('mastah');
$irc->loadModule('cvscheckout');

// connection
$irc->connect($config['irc_server'], $config['irc_port']);
$irc->setCtcpVersion($config['version']);
$irc->login($config['nick'], $config['real_name'], 8, $config['ident']);
$start=time();
$irc->join($config['channels']);
$irc->listen();
$irc->disconnect();
?>