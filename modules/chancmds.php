<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
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
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!die', $this, 'quit');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!time', $this, 'saytime');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!opme$', $this, 'onjoin');
        $this->actionid4 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!up$', $this, 'onjoin');
        $this->actionid5 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kick', $this, 'kick');
        $this->actionid6 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!ban', $this, 'ban');
        $this->actionid7 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!kb', $this, 'kickban');
        $this->actionid8 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!unban', $bot, 'unban');
        $this->actionid9 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!op', $this, 'op');
        $this->actionid10 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deop', $this, 'deop');
        $this->actionid11 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!invite', $this, 'invite');
        $this->actionid12 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!topic', $this, 'topic');
        $this->actionid13 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!join', $this, 'join_channel');
        $this->actionid14 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!part', $this, 'part_channel');
        $this->actionid15 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!help', $this, 'help');
        $this->actionid16 = $irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'onjoin');
        $this->actionid17 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!uptime$', $this, 'uptime');
        $this->actionid18 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!news$', $this, 'getnews');
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
    }
    
    //===============================================================================================
    function join_channel(&$irc, &$data)
    {
        global $config;
        global $bot;
        $chan=$data->messageex[1];
        $key=$data->messageex[2];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_OPERATOR)) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Attempting to join '.$chan.'...');
                $irc->join($chan,$key);
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_OPERATOR)) {
                $irc->part($chan,'Requested by '.$requester);
            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_VOICE)) {
                
                $irc->setTopic($data->channel,substr($topic,7,strlen($topic)-7));
            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->invite($toinvite,$data->channel);
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Inviting '.$toinvite.' (Requested by '.$requester.')...');

            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
                $irc->quit('Killed by '.$data->nick);
            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);
            if ($result !== false && !$irc->isOpped($data->channel, $data->nick)) {
                $level = $bot->get_level($data->nick);
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_OPERATOR) && !$irc->isOpped($data->channel, $tobeopped)) {
                $irc->op($data->channel, $tobeopped);
            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_OPERATOR) && $irc->isOpped($data->channel, $tobedeopped)) {
                $irc->deop($data->channel, $tobedeopped);
            }
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
        
        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->kick($data->channel, $tobekicked, '['.$requester.']'.$reason);
            }
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

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_VOICE)) {

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
        global $bot;
        $requester = $data->nick;
        $tobebanned = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_VOICE)) {
                $irc->unban($data->channel, $tobebanned);
            }
        }
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
    function saytime(&$irc,&$data)
    {
        global $config;
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'At the tone, the time will be: '.date('H:iT').'. *ding*');
    }
}
?>