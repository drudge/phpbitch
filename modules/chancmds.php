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
 
class Net_SmartIRC_module_chancmds
{
    var $name = 'chancmds';
    var $description = 'this module adds basically all darkbot channel commands.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!die', $this, 'quit');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!time', $this, 'saytime');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!opme$', $this, 'onjoin');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!up$', $this, 'onjoin');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kick', $this, 'kick');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ban', $this, 'ban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kb', $this, 'kickban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!unban', $this, 'unban');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!op', $this, 'op');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deop', $this, 'deop');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!invite', $this, 'invite');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!topic', $this, 'topic');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!join', $this, 'join_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!part', $this, 'part_channel');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!help', $this, 'help');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'onjoin');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!dns', $this, 'dns');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!news$', $this, 'getnews');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!down$', $this, 'down');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ping$', $this, 'ping');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================
    function join_channel(&$irc, &$data)
    {
        global $bot;
        $chan = $data->messageex[1];
        if (isset($data->messageex[2])) {
            $key = $data->messageex[2];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Attempting to join '.$chan.'...');
            
            if (isset($key)) {
                $irc->join($chan,$key);
            } else {
                $irc->join($chan);
            }
        }
    }
    //===============================================================================================
    function part_channel(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $chan=$data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_MASTER)) {
            $irc->part($chan,'Requested by '.$requester);
        }
    }
    //===============================================================================================
    function topic(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $topic = $data->message;

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_VOICE)) {
            
            $irc->setTopic($data->channel,substr($topic,7,strlen($topic)-7));
        }
    }
    //===============================================================================================
    function invite(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $toinvite = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_VOICE)) {
            $irc->invite($toinvite,$data->channel);
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Inviting '.$toinvite.' (Requested by '.$requester.')...');
        }
    }
    //===============================================================================================
    function quit(&$irc, &$data)
    {
        global $bot;
        
         // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) == USER_LEVEL_MASTER)) {
            $irc->quit('Killed by '.$data->nick);
        }
    }
    //===============================================================================================
    function onjoin(&$irc, &$data)
    {
        global $bot;
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        if ($result !== false && !$irc->isOpped($data->channel, $data->nick)) {
            $level = $bot->get_level($result);
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
                case USER_LEVEL_BOT:
                    $irc->mode($data->channel, '+ov '.$data->nick.' '.$data->nick);
                    break;
            }
        }
    }
    //===============================================================================================
    function op(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $tobeopped = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_OPERATOR) && !$irc->isOpped($data->channel, $tobeopped)) {
            $irc->op($data->channel, $tobeopped);
        }
    }
    //===============================================================================================
    function deop(&$irc, &$data)
    {
        global $bot;
        $requester = $data->nick;
        $tobedeopped = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if (isset($irc->channel[strtolower($data->channel)]->users[strtolower($tobedeopped)])) {
            $victim = &$irc->channel[strtolower($data->channel)]->users[strtolower($tobedeopped)];
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        if (($result !== false) &&
            ($bot->get_level($result) >= USER_LEVEL_OPERATOR) &&
            ($bot->get_level($newresult) < USER_LEVEL_MASTER) &&
            ($irc->isOpped($data->channel, $tobedeopped))) {
            
            $irc->deop($data->channel, $tobedeopped);
        }
    }
    //===============================================================================================
    function down(&$irc, &$data)
    {
        global $bot;
        $tobedeopped = $data->nick;
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if (($result !== false) &&
            ($bot->get_level($result) >= USER_LEVEL_OPERATOR) &&
            ($irc->isOpped($data->channel, $tobedeopped)) &&
            ($bot->get_level($tobedeopped) < USER_LEVEL_MASTER)) {
            $irc->deop($data->channel, $tobedeopped);
        }
    }
    //===============================================================================================
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
        
        if (isset($irc->channel[strtolower($data->channel)]->users[strtolower($tobekicked)])) {
            $victim = &$irc->channel[strtolower($data->channel)]->users[strtolower($tobekicked)];
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        if (($result !== false) &&
            ($bot->get_level($result) >= USER_LEVEL_VOICE) &&
            ($bot->get_level($newresult) < USER_LEVEL_MASTER)) {
            
            $irc->kick($data->channel, $tobekicked, '['.$requester.']'.$reason);
        }
    }
    //===============================================================================================
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
        
        if (isset($irc->channel[$data->channel]->users[strtolower($tobebanned)])) {
            $victim = &$irc->channel[strtolower($data->channel)]->users[strtolower($tobebanned)];
        } else {
            return;
        }
        
        $newdata->host = $victim->host;
        $newdata->nick = $victim->nick;
        $newdata->ident = $victim->ident;
        $newdata->channel = $data->channel;
        $newresult = $bot->reverseverify($irc, $newdata);
        
        if (($result !== false) &&
            ($bot->get_level($result) >= USER_LEVEL_VOICE) &&
            ($bot->get_level($newresult) < USER_LEVEL_MASTER)) {
            
            $nick = $victim->nick;
            $ident = $victim->ident;
            $host = $victim->host;
            
            $lam0r = $nick.'!'.$ident.'@'.$host;
            
            $lam0r = preg_replace('/^[^!]+![~\-+]?([^@]+)@.*\.(\w+\.\w+)$/', '*!*\1@*.\2', $lam0r);
            
            $irc->ban($data->channel, $lam0r);
        }
    }
    //===============================================================================================
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
            $irc->kick($data->channel, $tobebanned, 'Banned: '.$reason);
        }
    }
    //===============================================================================================
    function unban(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $tobeunbanned = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_VOICE)) {
            $irc->unban($data->channel, $tobeunbanned);
        }
    }
    //===============================================================================================       
    function help(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        //$lines = file('help.txt');
        
        $line="Fuck off, i ain't helping you, lam0r!";
        //foreach($lines as $line) {
            $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line);
        //}
    }
        //===============================================================================================       
    function getnews(&$irc, &$data)
    {
       /* $file = "http://gtk.php.net";
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
        }*/
    }
    //===============================================================================================
    function saytime(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'At the tone, the time will be: '.date('H:iT').'. *ding*');
    }
    //===============================================================================================
    function ping(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, '*PONG*');
    }
    //===============================================================================================
    function dns(&$irc,&$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $tolookup = $data->messageex[1];
        $user = &$irc->channel[$data->channel]->users[strtolower($tolookup)];
        $ip = gethostbyname($user->host);
        
        if (!empty($ip)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$tolookup.'\'s IP is '.$ip.'.');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I dunno '.$tolookup.'\'s IP.');
        }
    }
}
?>