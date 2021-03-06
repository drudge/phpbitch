<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2003 Nicholas 'DrUDgE' Penree <drudge@x-php.net>
 *                    Mirco 'meebey' Bauer <mail@meebey.net>
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
 
class Net_SmartIRC_module_chancmds
{
    var $name = 'chancmds';
    var $version = '$Revision$';
    var $description = 'this module adds basically all darkbot channel commands.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>, Mirco \'meebey\' Bauer <mail@meebey.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    var $_candidates = array();
    var $_op_count = 0;
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!die', $this, 'quit');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!time', $this, 'saytime');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!opme$', $this, 'onjoin');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!up$', $this, 'onjoin');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!kick', $this, 'kick');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!ban', $this, 'ban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!kb', $this, 'kickban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!unban', $this, 'unban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!op', $this, 'op');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!deop', $this, 'deop');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!voice', $this, 'voice');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!devoice', $this, 'devoice');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!invite', $this, 'invite');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!topic', $this, 'topic');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!cycle', $this, 'cycle_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!join', $this, 'join_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!part', $this, 'part_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!help', $this, 'help');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!dns', $this, 'dns');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!news$', $this, 'getnews');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!down$', $this, 'down');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!ping$', $this, 'ping');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'onjoin');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function join_channel(&$irc, &$data)
    {
        global $bot;
        $chan = $data->messageex[1];
        if (isset($data->messageex[2])) {
            $key = $data->messageex[2];
        }
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Attempting to join '.$chan.'...');
            
            if (isset($key)) {
                $irc->join($chan, $key);
            } else {
                $irc->join($chan);
            }
        }
    }

    function cycle_channel(&$irc, &$data)
    {
        global $bot;
        $chan = $data->messageex[1];
        if (isset($data->messageex[2])) {
            $key = $data->messageex[2];
        }
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {            
            $irc->part($chan, 'Cycling, be right back ;)');
            if (isset($key)) {
                $irc->join($chan, $key);
            } else {
                $irc->join($chan);
            }
        }
    }

    function part_channel(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $chan=$data->messageex[1];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->part($chan, 'Requested by '.$requester);
        }
    }

    function topic(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $topic = $data->message;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_VOICE)) {
            $irc->setTopic($data->channel, substr($topic, 7, strlen($topic)-7));
        }
    }

    function invite(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $toinvite = $data->messageex[1];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_VOICE)) {
            $irc->invite($toinvite,$data->channel);
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Inviting '.$toinvite.' (Requested by '.$requester.')...');
        }
    }

    function quit(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->quit('Killed by '.$data->nick);
        }
    }

    function onjoin(&$irc, &$data)
    {
        global $bot;
        global $config;
        if(!$bot->isMastah($irc, $data) &&
           $irc->isOpped($data->channel, $config['friend_bots'][0])) {
            return;
        }
        
        // don't try to op ourself when we join
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        
        $result = $bot->reverseverify($irc, $data);
        if ($result !== false && !$irc->isOpped($data->channel, $data->nick)) {
            $id = $irc->registerTimehandler(3000, $this, "_do_op");
            $this->_op_count++;
            $this->_candidates[$this->_op_count]['nick'] = $data->nick;
            $this->_candidates[$this->_op_count]['channel'] = $data->channel;
            $this->_candidates[$this->_op_count]['handler_id'] = $id;
            $this->_candidates[$this->_op_count]['result'] = $result;
        }
    }

    function op(&$irc, &$data)
    {
        global $bot;
        if (!$irc->isOpped($data->channel)) {
            return;
        }
                
        $requester = $data->nick;
        if ($bot->isAuthorized($irc, $requester, $data->channel, USER_LEVEL_OPERATOR)) {
            $candidate_count = count($data->messageex);
            if ($candidate_count > 1) {
                for ($i = 1; $i < $candidate_count; $i++) {
                    $toop = $data->messageex[$i];
                    if (($irc->isJoined($data->channel, $toop)) &&
                        (!$irc->isOpped($data->channel, $toop))) {
                        $irc->op($data->channel, $toop);
                    }
                }
            } else {
                $irc->op($data->channel, $requester);
            }
        }
    }

    function deop(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $tobedeopped = $data->messageex[1];
        
        
        if ($irc->isJoined($data->channel, $tobedeopped)) {
            $victim =& $irc->getUser($data->channel, $tobedeopped);
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        $result = $bot->reverseverify($irc, $data);
        if (($result !== false) &&
            ($bot->getLevel($result, $data->channel) >= USER_LEVEL_OPERATOR) &&
            ($bot->getLevel($newresult, $data->channel) < USER_LEVEL_MASTER) &&
            ($irc->isOpped($data->channel, $tobedeopped))) {
            
            $irc->deop($data->channel, $tobedeopped);
        }
    }

    function voice(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $tobevoiced = $data->messageex[1];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_OPERATOR) &&
            !$irc->isVoiced($data->channel, $tobevoiced)) {
            
            $irc->voice($data->channel, $tobevoiced);
        }
    }

    function devoice(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $tobedevoiced = $data->messageex[1];
        
        if ($irc->isJoined($data->channel, $tobedevoiced)) {
            $victim =& $irc->getUser($data->channel, $tobedevoiced);
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        $result = $bot->reverseverify($irc, $data);
        if (($result !== false) &&
            ($bot->getLevel($result, $data->channel) >= USER_LEVEL_OPERATOR) &&
            ($bot->getLevel($newresult, $data->channel) < USER_LEVEL_MASTER) &&
            ($irc->isVoiced($data->channel, $tobedevoiced))) {
            
            $irc->devoice($data->channel, $tobedevoiced);
        }
    }

    function down(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $tobedeopped = $data->nick;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_OPERATOR) &&
            ($irc->isOpped($data->channel, $tobedeopped))) {
            $irc->deop($data->channel, $tobedeopped);
        }
    }

    function kick(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $tobekicked = $data->messageex[1];
        
        // Get the reason
        $reason='';
        for($i=2;$i<count($data->messageex);$i++) {
            $reason.=' '.$data->messageex[$i];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($irc->isJoined($data->channel, $tobekicked)) {
            $victim =& $irc->getUser($data->channel, $tobekicked);
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        if (($result !== false) &&
            ($bot->getLevel($result, $data->channel) >= USER_LEVEL_VOICE) &&
            ($bot->getLevel($newresult, $data->channel) < USER_LEVEL_MASTER)) {
            
            $irc->kick($data->channel, $tobekicked, '['.$requester.']'.$reason);
        }
    }

    function ban(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($irc->isJoined($data->channel, $tobebanned)) {
            $victim =& $irc->getUser($data->channel, $tobebanned);
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        if (($result !== false) &&
            ($bot->getLevel($result, $data->channel) >= USER_LEVEL_VOICE) &&
            ($bot->getLevel($newresult, $data->channel) < USER_LEVEL_MASTER)) {
            
            $nick = $victim->nick;
            $ident = $victim->ident;
            $host = $victim->host;
            
            $lam0r = $nick.'!'.$ident.'@'.$host;
            
            $lam0r = preg_replace('/^[^!]+![~\-+]?([^@]+)@.*\.(\w+\.\w+)$/', '*!*\1@*.\2', $lam0r);
            
            $irc->ban($data->channel, $lam0r);
            
            // return true needed, kickban() wants to know if the ban() was allowed/succeful
            return true;
        }
    }

    function kickban(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];
        
        // Get the reason
        $reason='';
        for($i=2;$i<count($data->messageex);$i++) {
            $reason.=' '.$data->messageex[$i];
        }

        // banhandling and rights we all do in ban(), so lets use it!
        $result = $this->ban($irc, $data);
        if ($result) {
            $irc->kick($data->channel, $tobebanned, 'Banned: ['.$requester.']'.$reason);
        }
    }

    function unban(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $tobeunbanned = $data->messageex[1];
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_VOICE)) {
            $irc->unban($data->channel, $tobeunbanned);
        }
    }

    function help(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $lines = file('help.txt');
        
        foreach($lines as $line) {
            $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line);
        }
    }

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
        }
        */
    }

    function saytime(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'At the tone, the time will be: '.date('H:iT').'. *ding*');
    }

    function ping(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, '*PONG*');
    }

    function dns(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $tolookup = $data->messageex[1];
        $user =& $irc->getUser($data->channel, $tolookup);
        $ip = gethostbyname($user->host);
        
        if (!empty($ip)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$tolookup.'\'s IP is '.$ip.'.');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I dunno '.$tolookup.'\'s IP.');
        }
    }

    function _do_op(&$irc)
    {
         global $bot;
         
         foreach($this->_candidates as $key => $_candidate) {
         $level = $bot->getLevel($_candidate['result'], $_candidate['channel']);
            switch ($level) {
                case USER_LEVEL_NORMAL:
                case USER_LEVEL_FRIEND:
                    break;
                case USER_LEVEL_VOICE:
                    $irc->voice($_candidate['channel'], $_candidate['nick']);
                    break;
                case USER_LEVEL_OPERATOR:
                case USER_LEVEL_MASTER:
                    $irc->op($_candidate['channel'], $_candidate['nick']);
                    break;
                case USER_LEVEL_BOT:
                    $irc->mode($_candidate['channel'], '+ov '.$_candidate['nick'].' '.$_candidate['nick']);
                    break;
            }
            $irc->unregisterTimeid($_candidate['handler_id']);
            unset($this->_candidates[$key]);
        }
    }
}
?>