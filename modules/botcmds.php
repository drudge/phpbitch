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
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!uptime$', $this, 'uptime');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!nick', $this, 'setNick');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!wave', $this, 'wave');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!say', $this, 'say');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!act', $this, 'act');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!notice', $this, 'notice');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!phpversion', $this, 'php_version');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================       
    function uptime(&$irc, &$data)
    {
        global $start;
        $time=time()-$start;
        
        $weeks=$time/604800; 
        $days=($time%604800)/86400; 
        $hours=(($time%604800)%86400)/3600; 
        $minutes=((($time%604800)%86400)%3600)/60; 
        $seconds=(((($time%604800)%86400)%3600)%60); 
        
        $timestring = '';
        if (round($days)) {
            $timestring .= round($days).' day(s) ';
        }
        if (round($hours)) {
            $timestring .= round($hours).' hour(s) ';
        }
        if (round($minutes)) {
            $timestring .= round($minutes).' minute(s)';
        }
        if (!round($minutes) &&
            !round($hours) &&
            !round($days)) {
            $timestring .= round($seconds).' second(s)';
        }
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I have been running for '.$timestring);
    }
    //===============================================================================================
    function setNick(&$irc, &$data)
    {
        global $bot;
        $newnick = $data->messageex[1];
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        // need a valid channel for verify()
        $data->channel = $channel;
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->changeNick($newnick);
            $bot->log(SMARTIRC_DEBUG_NOTICE,'attempted to change nick to: '.$newnick);
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
        global $bot;
        $channel = $data->messageex[1];
        $message = '';
        for ($i = 2; $i < count($data->messageex); $i++) {
            $message .= ' '.$data->messageex[$i];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        // need a valid channel for verify()
        $data->channel = $channel;
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $channel, trim($message));
        }
    }
    //===============================================================================================
    function act(&$irc, &$data)
    {
        global $bot;
        $channel = $data->messageex[1];
        $message = '';
        for ($i = 2; $i < count($data->messageex); $i++) {
            $message .= ' '.$data->messageex[$i];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        // need a valid channel for verify()
        $data->channel = $channel;
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_ACTION,$channel,trim($message));
        }
    }
    //===============================================================================================
    function notice(&$irc, &$data)
    {
        global $bot;
        $channel = $data->messageex[1];
        $message = '';
        for ($i = 2; $i < count($data->messageex); $i++) {
            $message .= ' '.$data->messageex[$i];
        }
        
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        // need a valid channel for verify()
        $data->channel = $channel;
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_NOTICE,$channel,trim($message));
        }
    }
    //===============================================================================================
    function php_version(&$irc, &$data)
    {
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, phpversion());
    }
}
?>
