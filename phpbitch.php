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

// Include dependent files
include_once('config.php');
include_once('Net/SmartIRC.php');

// Initialize start time
$start=0;

function dbquery($query)
{
    global $mysql_db;
    global $mysql_server;
    global $mysql_username;
    global $mysql_password;
    global $mysql_link;
    global $irc;

    if (!is_resource($mysql_link))
        $mysql_link = mysql_connect($mysql_server, $mysql_username, $mysql_password);

    $result = mysql_db_query($mysql_db, $query, $mysql_link);
    return $result;
}
//===============================================================================================
function get_level($nick)
{
    $query = "SELECT level FROM dnsentries WHERE nickname='".$nick."'";
    $result = dbquery($query);
    $numrows = mysql_num_rows($result);

    if ($numrows > 0) {
        $row = mysql_fetch_array($result);
        return $row[0];
    }
    return 0;
}
//===============================================================================================
class PHPBitch
{
    function reverseverify(&$irc, $host, $nick)
    {
        $query = "SELECT nickname,dnsalias FROM dnsentries";
        $result = dbquery($query);

        $list = array();
        $foundnick = false;
        $userip = gethostbyname($host);
        while ($row = mysql_fetch_array($result)) {
            $dbip = gethostbyname($row['dnsalias']);
            $dbnickname = $row['nickname'];

            if ($userip == $dbip) {
                if ($dbnickname != $nick) {
                    $foundnick = $dbnickname;
                }
                break;
            }
        }

        if ($foundnick !== false) {
            $result = $this->verify($irc, $foundnick, $nick);
        } else {
            $result = $this->verify($irc, $nick);
        }

        if ($result !== false) {
            return $result;
        } else {
            return false;
        }
    }
    //===============================================================================================
    function verify(&$irc, $nickname, $ircnickname = null)
    {
        global $config;
        $who = $nickname;
        $loweredwho = strtolower($who);

        if ($ircnickname !== null) {
            $dbwho = $nickname;
            $who = $ircnickname;
            $loweredwho = strtolower($who);
        } else {
            $dbwho = $who;
        }

        if (isset($irc->channel[$data->channel]->users[$loweredwho])) {
            $query = "SELECT nickname,dnsalias FROM dnsentries WHERE nickname='".$dbwho."'";
            $result = dbquery($query);
            $numrows = mysql_num_rows($result);

            if ($numrows > 0) {
                $host = $irc->channel[$data->channel]->users[$loweredwho]->host;
                $ip = gethostbyname($host);

                $found = false;
                while ($row = mysql_fetch_array($result)) {
                    $dnsaliasip = gethostbyname($row['dnsalias']);

                    if ($dnsaliasip == $ip) {
                        return $row['nickname'];
                    }
                }
            }
            return false;
        }
    }
    //===============================================================================================
    function search_db(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $search = $data->messageex[1];
        $lowersearch = strtolower($data->messageex[1]);

        if (!empty($search)) {
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            dbquery("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            $result = dbquery($query);
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
    function kick(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobekicked = $data->messageex[1];
        
        // Get the reason
        $temp=explode(' ',$data->message);
        $reason='';
        for($i=2;$i<count($temp);$i++) {
            $reason.=' '.$temp[$i];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->kick($data->channel, $tobekicked, '['.$requester.']'.$reason);
            }
        }
    }
    //===============================================================================================
    function ban(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_VOICE)) {

                if (isset($irc->channel[$data->channel]->users[strtolower($tobebanned)])) {
                    // I guess this way its more clear ;)
                    $user = &$irc->channel[$data->channel]->users[strtolower($tobebanned)];
                    $nick = $user->nick;
                    $ident = $user->ident;
                    $host = $user->host;
                    
                    $lam0r = $nick.'!'.$ident.'@'.$host;

                    $lam0r = preg_replace('/^[^!]+![~\-+]?([^@]+)@.*\.(\w+\.\w+)$/', '*!*\1@*\2', $lam0r);

                    $irc->ban($data->channel, $lam0r);
                    return true;
                }
            }
        }
        
        return false;
    }
    //===============================================================================================
    function kickban(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];
        
        // banhandling and rights we all do in ban(), so lets use it!
        $result = $this->ban($irc, $data);
        if ($result) {
            $irc->kick($data->channel, $tobebanned, 'Banned: Requested by '.$requester);
        }
    }
    //===============================================================================================
    function unban(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->unban($data->channel, $tobebanned);
            }
        }
    }
    //===============================================================================================
    function google(&$irc, &$data)
    {
        // Get the search
        $temp=explode(' ',$data->message);
        $search='';
        for ($i=1;$i<count($temp);$i++) {
            $search.='+'.$temp[$i];
        }
        
        $question=trim($search,'+');
        if (!$question) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'No search string given.');
        } else {
            $fp = fsockopen('www.google.com', 80, $errno, $errstr, 30);
            if (!$fp) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Socket connection failed: '.$errstr);
            } else {
                fputs($fp, "GET /search?as_q=$question&num=100 HTTP/1.0\r\nHost: www.google.com\r\n\r\n");
                $page = '';
                while (!feof($fp)) {
                    $raw = fgets($fp);
                    $page .= $raw;
                }
                
                $ex1 = explode('<font color=#008000>', $page);
                for($i=0;$i<count($ex1);$i++) {
                    $ex2[] = explode(' - ', $ex1[$i]);
                }
                
                $irc->setSenddelay(500);
                $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'Google Search Results for: \''.$question.'\'');
                
                if (count($ex2) >=5) {
                    $count=5;
                } else {
                    $count=count($ex2);
                }
                
                if ($count > 0) {
                    for($i=1;$i<$count;$i++) {
                        $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'http://'.$ex2[$i][0]);
                    }
                } else {
                    $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'No results for \''.$question.'\'.');
                }
                
                $irc->setSenddelay(250);
                fclose($fp);
            }
        }
    }
    //===============================================================================================
    function op(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobeopped = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_OPERATOR) && !$irc->isOpped($data->channel, $tobeopped)) {
                $irc->op($data->channel, $tobeopped);
            }
        }
    }
    //===============================================================================================
    function deop(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $tobedeopped = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_OPERATOR) && $irc->isOpped($data->channel, $tobedeopped)) {
                $irc->deop($data->channel, $tobedeopped);
            }
        }
    }
    //===============================================================================================
    function onjoin(&$irc, &$data)
    {
       global $config;

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);
            if ($result !== false && !$irc->isOpped($data->channel, $data->nick)) {
                $level = get_level($data->nick);
                switch ($level) {
                    case USER_LEVEL_NORMAL:
                    case USER_LEVEL_FRIEND:
                        break;
                    case USER_LEVEL_VOICE:
                        $irc->voice($data->channel, $data->nick);
                        break;
                    case USER_LEVEL_OPERATOR:
                        $irc->op($data->channel, $data->nick);
                        break;
                    case USER_LEVEL_MASTER:
                        $irc->mode($data->channel, '+ov '.$data->nick.' '.$data->nick);
                        break;
                }
            }
        }
    }
    //===============================================================================================    
    function who(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $lookupfor = $data->messageex[1];
        $lowerlookupfor = strtolower($data->messageex[1]);

        if (isset($irc->channel[$data->channel]->users[$lowerlookupfor])) {
            $host = $irc->channel[$data->channel]->users[$lowerlookupfor]->host;
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is not in this channel');
            return;
        }

        $result = $this->reverseverify($irc, $host, $lookupfor);

        if ($result !== false) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is '.$result.'['.gethostbyname($host).']');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $lookupfor.' is not a registered user of '.$data->channel.'.');
        }
    }
    //===============================================================================================
    function quit(&$irc, &$data)
    {
        global $config;

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $irc->quit('Killed by '.$data->nick);
            }
        }
    }
    //===============================================================================================
    function join_channel(&$irc, &$data)
    {
        global $config;
        $chan=$data->messageex[1];
        $key=$data->messageex[2];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_OPERATOR)) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Attempting to join '.$chan.'...');
                $irc->join($chan,$key);
            }
        }
    }
    //===============================================================================================
    function part_channel(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $chan=$data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_OPERATOR)) {
                $irc->part($chan,'Requested by '.$requester);
            }
        }
    }
    //===============================================================================================
    function topic(&$irc, &$data)
    {
        global $config;
        $topic=$data->message;
        // don't verify ourself
        
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_VOICE)) {
                
                $irc->setTopic($data->channel,substr($topic,7,strlen($topic)-7));
            }
        }
    }
    //===============================================================================================
    function invite(&$irc, &$data)
    {
        global $config;
        $requester = $data->nick;
        $toinvite = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->invite($toinvite,$data->channel);
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Inviting '.$toinvite.' (Requested by '.$requester.')...');

            }
        }
    }
    //===============================================================================================
    function adduser(&$irc, &$data)
    {
        global $config;
        $nick = $data->messageex[1];
        $host = $data->messageex[2];
        $level = $data->messageex[3];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "INSERT INTO dnsentries( `nickname`,`dnsalias`,`level`) VALUES('".$nick."','".$host."','".$level."')";
                $res = dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$nick.' as '.$host.' with a level of '.$level);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================
    function learn(&$irc, &$data)
    {
        global $config;
        $usersquery = $data->messageex[1];
        //$response = $data->messageex[2];
        
        // Get the response
        $temp=explode(' ',$data->message);
        $response='';
        for ($i=2;$i<count($temp);$i++)
            $response.=' '.$temp[$i];
            
         $response=trim($response);
         
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "INSERT INTO brain( `query`,`response`,`count`) VALUES('".$usersquery."','".$response."','0')";
                $res = dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$usersquery.' as '.$response);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================
    function deluser(&$irc, &$data)
    {
        global $config;
        $nick = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "DELETE FROM dnsentries WHERE nickname='".$nick."'";
                $res = dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Deleted '.$nick.' from registered users database.');
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================
    function forget(&$irc, &$data)
    {
        global $config;
        $usersquery = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $this->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "DELETE FROM brain WHERE query='".$usersquery."'";
                $res = dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I will now plead to the 5th about '.$usersquery);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================       
    function saytime(&$irc,&$data)
    {
        global $config;
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'At the tone, the time will be: '.date('H:iT').'. *ding*');
    }
    //===============================================================================================       
    function getnews(&$irc, &$data)
    {
        /*
        $file = "http://gtk.php.net";
        $open = fopen($file, "r");
        $search = fread($open, 20000);
        fclose($open);
        $search = preg_replace("/.*?<h1>News<\/h1>/ims", '', $search);
        preg_match("/(.*?)<h1>Resources<\/h1>/ims", $search, $r);
        #echo $r[1] . "\n";
        $search = preg_replace("/<tr bgcolor=\"#000000\"><td><img
                         src=\"\/gifs\/spacer.gif\" width=\"1\" height=\"1\" border=\"0\"
                         alt=\"\" ><\/td><\/tr>/ims", '---', $r[1]);
        $search = strip_tags($search);
        $search = preg_replace("/[ ]{3,}/ims", '', $search);
        $news = '';
        $line = strtok($search, "\n");
        $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "Latest News [http://gtk.php.net]");
        while( $line ) {
                 $line = substr(trim($line),0,30)."...";
               // if ( !(empty($line)) )
               //          $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line . "\n");
                // $line = strtok("\n");
                 $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line);
        }
        //echo $news . "\n";
        
        foreach($items as $item)
        {
            $news=trim($item);
            $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "$news...");
            //sleep(2);
        }
        */
    }
    //===============================================================================================       
    function help(&$irc, &$data)
    {
        $lines=file('help.txt');
        $irc->setSenddelay(500);

        foreach($lines as $linenums => $line) {
            $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line);
        }

        $irc->setSenddelay(250);
    }
    //===============================================================================================       
    function uptime(&$irc,&$data)
    {
        global $config;
        global $start;
        $time=time()-$start;
        
        $weeks=$time/604800; 
        $days=($time%604800)/86400; 
        $hours=(($time%604800)%86400)/3600; 
        $minutes=((($time%604800)%86400)%3600)/60; 
        $seconds=(((($time%604800)%86400)%3600)%60); 
        
        if(round($days)) $timestring.=round($days).' day(s) '; 
        if(round($hours)) $timestring.=round($hours).' hour(s) '; 
        if(round($minutes)) $timestring.=round($minutes).' minute(s)'; 
        if(!round($minutes)&&!round($hours)&&!round($days)) $timestring.=round($seconds).' second(s)'; 

        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I have been running for '.$timestring);
    }
    //===============================================================================================       
}

$bot = &new PHPBitch();
$irc = &new Net_SmartIRC();
$irc->setLogdestination(SMARTIRC_FILE);
$irc->setLogfile('./phpbitch.log');
$irc->setChannelSynching(true);
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setUseSockets(true);
$irc->setAutoRetry(true);
$irc->setAutoReconnect(true);
$irc->setReceiveTimeout(600);
$irc->setTransmitTimeout(600);

// Functionality
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!who ', $bot, 'who');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!die', $bot, 'quit');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!time', $bot, 'saytime');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!search', $bot, 'search_db');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!opme$', $bot, 'onjoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!up$', $bot, 'onjoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kick', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ban', $bot, 'ban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kb', $bot, 'kickban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!unban', $bot, 'unban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!adduser', $bot, 'adduser');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deluser', $bot, 'deluser');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!op', $bot, 'op');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deop', $bot, 'deop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!uptime$', $bot, 'uptime');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!news$', $bot, 'getnews');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!learn', $bot, 'learn');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!forget', $bot, 'forget');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!invite', $bot, 'invite');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!topic', $bot, 'topic');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!join', $bot, 'join_channel');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!part', $bot, 'part_channel');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!help', $bot, 'help');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!google', $bot, 'google');

// connection
$irc->connect($config['irc_server'], $config['irc_port']);
$irc->setCtcpVersion($config['version']);
$irc->login($config['nick'], $config['real_name'], 8, $config['ident']);
$start=time();
$irc->join($config['channels']);
$irc->listen();
$irc->disconnect();
?>