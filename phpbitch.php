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

define('PHPBITCH_VERSION', '1.0 ($Revision$)');

// Include dependent files
include_once('config.php');
include_once('Net/SmartIRC.php');
include_once('MDB.php');

// Initialize start time
$start=0;

function mdbError(&$errorobj)
{
    global $irc;
    
    $error = $errorobj->getUserinfo();
    $irc->log(SMARTIRC_DEBUG_NOTICE, 'DEBUG_NOTICE: DB error: '.$error);
    return $error;
}

//===============================================================================================
class PHPBitch
{
    //===============================================================================================
    function get_level($nick)
    {
        global $mdb;
        
        $query = "SELECT level FROM users WHERE nickname='".$nick."'";
        $result = $mdb->query($query);
        if (MDB::isError($result)) {
            mdbError($result);
            return;
        }
        
        $numrows = $mdb->numRows($result);
        
        if ($numrows > 0) {
            $row = $mdb->fetchRow($result);
            return $row['level'];
        }
        
        return false;
    }
    
    function getLevel($nick, $channel)
    {
        global $mdb;
        
        $query = "SELECT level FROM users_levels WHERE user = '".$nick."' AND (channel = '".$channel."' OR channel = '*') ORDER BY channel ASC";
        $result = $mdb->query($query);
        if (MDB::isError($result)) {
            mdbError($result);
            return;
        }
        
        if ($mdb->numRows($result) > 0) {
            $row = $mdb->fetchRow($result);
            $dblevel = $row['level'];
            return $dblevel;
        } else {
            return false;
        }
    }
    
    function isAuthorized(&$irc, $ircnick, $channel, $level)
    {
        global $mdb;
        
        if (!$irc->isJoined($channel, $ircnick)) {
            return false;
        }
        
        $user = &$irc->channel[strtolower($channel)]->users[strtolower($ircnick)];
        
        // check if the user is recognized and has right host, ident etc..
        $data->nick = $ircnick;
        $data->host = $user->host;
        $data->ident = $user->ident;
        $result = $this->reverseverify($irc, $data);
        
        if ($result !== false) {
            $dbnick = $result;
            $query = "SELECT level FROM users_levels WHERE user = '".$dbnick."' AND (channel = '".$channel."' OR channel = '*') ORDER BY channel ASC";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            
            $row = $mdb->fetchRow($result);
            $dblevel = $row['level'];
            
            // we know which level, but is this user authorized?
            if ($dblevel >= $level) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    //===============================================================================================
    function reverseverify(&$irc, &$data)
    {
        global $mdb;
        
        $query = "SELECT nickname, host FROM users WHERE ident = '".$data->ident."'";
        $result = $mdb->query($query);
        if (MDB::isError($result)) {
            mdbError($result);
            return;
        }
        
        $list = array();
        $foundnick = false;
        $userip = gethostbyname($data->host);
        while ($row = $mdb->fetchInto($result)) {
            $dbip = gethostbyname($row['host']);
            $dbnickname = $row['nickname'];
            
            if ($userip == $dbip) {
                if ($dbnickname != $data->nick) {
                    $foundnick = $dbnickname;
                } else {
                    $foundnick = $data->nick;
                }
                break;
            }
        }
        
        if ($foundnick !== false) {
            return $foundnick;
        } else {
            return false;
        }
    }
    //===============================================================================================
    function verify(&$irc, &$data, $dbnickname = null)
    {
        global $config;
        global $mdb;
        
        $who = $data->nick;
        $loweredwho = strtolower($who);
        $channel = $data->channel;
        $loweredchannel = strtolower($channel);
        $ident = $data->ident;
        
        if ($dbnickname !== null) {
            $dbwho = $dbnickname;
        } else {
            $dbwho = $data->nick;
        }
        
        if (isset($irc->channel[$loweredchannel]->users[$loweredwho])) {
            $query = "SELECT nickname,ident,host FROM users WHERE nickname='".$dbwho."'";
            $result = $mdb->query($query);
            if (MDB::isError($result)) {
                mdbError($result);
                return;
            }
            $numrows = $mdb->numRows($result);
            
            if ($numrows > 0) {
                $host = $irc->channel[$loweredchannel]->users[$loweredwho]->host;
                $ip = gethostbyname($host);
                
                $found = false;
                while ($row = $mdb->fetchInto($result)) {
                    $dbident = $row['ident'];
                    $dnsaliasip = gethostbyname($row['host']);
                    
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
            if (isset($irc->channel[strtolower($data->channel)]->users[strtolower($bot)])) {
                $user = &$irc->channel[strtolower($data->channel)]->users[strtolower($bot)];
                
                if ($this->isAuthorized($irc, $user->nick, $data->channel, USER_LEVEL_BOT) &&
                    $irc->isOpped($data->channel, $user->nick)) {
                    $candidates[] = $user->nick;
                }
            }
        }
        
        if (isset($candidates[0]) &&
            $candidates[0] == $irc->_nick) {
            // ok, it's showtime! we are the mastah _and_ we are op!!!
            return true;
        } else {
            return false;
        }
    }
    
    function show_synctime(&$irc, &$data)
    {
        $channel = &$irc->getChannel($data->channel);
        $irc->message(SMARTIRC_TYPE_ACTION, $data->channel, 'finished syncing to '.$data->channel.' in '.round($channel->synctime, 2).' secs');
    }
}

$bot = &new PHPBitch();
$irc = &new Net_SmartIRC();

$mdb = &MDB::connect($config['db_dsn']);
if (MDB::isError($mdb)) {
    mdbError($mdb);
    die('DB error: '.$mdb->getUserinfo());
}
$mdb->setFetchMode(MDB_FETCHMODE_ASSOC);

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
$irc->setSenddelay($config['send_delay']);

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
$irc->loadModule('hex_ip');
$irc->loadModule('log');
$irc->loadModule('ident');
$irc->loadModule('debug');
$irc->loadModule('versions');

$irc->registerActionhandler(SMARTIRC_TYPE_BANLIST, 'End of Channel Ban List', $bot, 'show_synctime');

// connection
$irc->connect($config['irc_server'], $config['irc_port']);
$irc->setCtcpVersion($config['version']);
$irc->login($config['nick'], $config['real_name'], 8, $config['ident']);
$start=time();
$irc->join($config['channels']);
$irc->listen();
$irc->disconnect();
?>