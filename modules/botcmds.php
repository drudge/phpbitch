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
 
class Net_SmartIRC_module_botcmds
{
    var $name = 'botcmds';
    var $description = 'this module has some bot commands.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid1;
    var $actionid2;
    var $actionid3;
    var $actionid4;
    var $actionid5;
    var $actionid6;

    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!uptime$', $this, 'uptime');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!nick', $this, 'setNick');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!wave', $this, 'wave');
        $this->actionid4 = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!say', $this, 'say');
        $this->actionid5 = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!act', $this, 'act');
        $this->actionid6 = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!notice', $this, 'notice');

    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
        $irc->unregisterActionid($this->actionid3);
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
    function setNick(&$irc, &$data)
    {
        global $config;
        global $bot;
        $newnick = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data);
        
        if ($result !== false && ($bot->get_level($result) >= USER_LEVEL_MASTER)) {
            $irc->changeNick($newnick);
        }
    }
    //===============================================================================================
    function wave(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $crap = $data->messageex[1];
        $nick = $data->messageex[2];
        
        if (!empty($nick)) {
            $irc->message(SMARTIRC_TYPE_ACTION, $data->channel, 'waves '.$crap.' '.$nick.'.');
        } else {
            $irc->message(SMARTIRC_TYPE_ACTION, $data->channel, 'waves to everyone.');
        }
    }
    //===============================================================================================
    function say(&$irc, &$data)
    {
        global $config;
        global $bot;
        $crap = explode(' ',$data->message);
        // don't verify ourself
        $channel = $crap[1];
        $message = '';
        for ($i=2; $i<count($crap); $i++) {
            $message.=' '.$crap[$i];
        }

        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        $result = $bot->reverseverify($irc, $data->host, $data->nick);

        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {

            $irc->message(SMARTIRC_TYPE_CHANNEL,$channel,trim($message));
        }
    }
    //===============================================================================================
    function act(&$irc, &$data)
    {
        global $config;
        global $bot;
        $crap = explode(' ',$data->message);
        // don't verify ourself
        $channel = $crap[1];
        $message = '';
        for ($i=2; $i<count($crap); $i++) {
            $message.=' '.$crap[$i];
        }

        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        $result = $bot->reverseverify($irc, $data->host, $data->nick);

        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {

            $irc->message(SMARTIRC_TYPE_ACTION,$channel,trim($message));
        }
    }
    //===============================================================================================
    function notice(&$irc, &$data)
    {
        global $config;
        global $bot;
        $crap = explode(' ',$data->message);
        // don't verify ourself
        $channel = $crap[1];
        $message = '';
        for ($i=2; $i<count($crap); $i++) {
            $message.=' '.$crap[$i];
        }

        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        $result = $bot->reverseverify($irc, $data->host, $data->nick);

        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {

            $irc->message(SMARTIRC_TYPE_NOTICE,$channel,trim($message));
        }
    }


}
?>