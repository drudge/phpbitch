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
    
    var $actionid1;
    var $actionid2;
    var $actionid3;
    var $actionid4;
    var $actionid5;
    var $actionid6;
    var $actionid7;
    var $actionid8;
    var $actionid9;
    var $actionid9;
    var $actionid10;
    var $actionid12;
    var $actionid13;
    var $actionid14;
    var $actionid15;
    var $actionid16;
    var $actionid17;
    var $actionid18;
    var $actionid19;
    var $actionid20;
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!die', $this, 'quit');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!time', $this, 'saytime');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!opme$', $this, 'onjoin');
        $this->actionid4 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!up$', $this, 'onjoin');
        $this->actionid5 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kick', $this, 'kick');
        $this->actionid6 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ban', $this, 'ban');
        $this->actionid7 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kb', $this, 'kickban');
        $this->actionid8 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!unban', $this, 'unban');
        $this->actionid9 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!op', $this, 'op');
        $this->actionid10 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deop', $this, 'deop');
        $this->actionid11 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!invite', $this, 'invite');
        $this->actionid12 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!topic', $this, 'topic');
        $this->actionid13 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!join', $this, 'join_channel');
        $this->actionid14 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!part', $this, 'part_channel');
        $this->actionid15 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!help', $this, 'help');
        $this->actionid16 = $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'onjoin');
        $this->actionid17 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!dns', $this, 'dns');
        $this->actionid18 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!news$', $this, 'getnews');
        $this->actionid19 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!down$', $this, 'down');
        $this->actionid20 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ping$', $this, 'ping');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
        $irc->unregisterActionid($this->actionid3);
        $irc->unregisterActionid($this->actionid4);
        $irc->unregisterActionid($this->actionid5);
        $irc->unregisterActionid($this->actionid6);
        $irc->unregisterActionid($this->actionid7);
        $irc->unregisterActionid($this->actionid8);
        $irc->unregisterActionid($this->actionid9);
        $irc->unregisterActionid($this->actionid10);
        $irc->unregisterActionid($this->actionid11);
        $irc->unregisterActionid($this->actionid12);
        $irc->unregisterActionid($this->actionid13);
        $irc->unregisterActionid($this->actionid14);
        $irc->unregisterActionid($this->actionid15);
        $irc->unregisterActionid($this->actionid16);
        $irc->unregisterActionid($this->actionid17);
        $irc->unregisterActionid($this->actionid18);
        $irc->unregisterActionid($this->actionid19);
        $irc->unregisterActionid($this->actionid20);
    }
    
    //===============================================================================================
    function join_channel(&$irc, &$data)
    {
        global $config;
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
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_OPERATOR)) {
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
        global $config;
        global $bot;
        $requester = $data->nick;
        $chan=$data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_OPERATOR)) {
            $irc->part($chan,'Requested by '.$requester);
        }
    }
    //===============================================================================================
    function topic(&$irc, &$data)
    {
        global $config;
        global $bot;
        $topic=$data->message;
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
        global $config;
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
        global $config;
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
        global $config;
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
        global $config;
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
        global $config;
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
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_OPERATOR) && $irc->isOpped($data->channel, $tobedeopped) && $bot->get_level($tobedeopped) < USER_LEVEL_MASTER) {
            $irc->deop($data->channel, $tobedeopped);
        }
    }
    //===============================================================================================
    function kick(&$irc, &$data)
    {
        global $config;
        global $bot;
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
        global $config;
        global $bot;
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
            
            $lam0r = preg_replace('/^[^!]+![~\-+]?([^@]+)@.*\.(\w+\.\w+)$/', '*!*\1@*\2', $lam0r);
            
            $irc->ban($data->channel, $lam0r);
        }
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
        global $bot;
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
        }
        
        $lines=file('help.txt');
        
        // $line="Fuck off, i ain't helping you, lam0r!";
        foreach($lines as $line) {
            $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $line);
        }
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
        
        global $config;
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