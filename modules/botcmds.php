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
    var $version = '$Revision$';
    var $description = 'this module has some bot commands.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    var $start_time;
    
    function module_init(&$irc)
    {
        $this->start_time  = time();
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!uptime$', $this, 'uptime');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!nick', $this, 'setNick');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!wave', $this, 'wave');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!say', $this, 'say');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!act', $this, 'act');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '^!notice', $this, 'notice');
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
        $duration = time() - $this->start_time;

        // Convert the interger to an array of segments
        $seconds = (int)$time;
        
        // Define our periods
        $periods = array (
                    'weeks'    => 604800,
                    'days'     => 86400,
                    'hours'    => 3600,
                    'minutes'  => 60,
                    'seconds'  => 1
                    );

        // Loop through
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);

            if ($count == 0) {
                continue;
            }
            $values[$period] = $count;
            $seconds = $seconds % $value;
        }

        // Loop through the interval array
        foreach ($duration as $key => $value) {
            // Chop the end of the duration key
            $segment_name = substr($key, 0, -1);

            // Create our segment in the format of eg. '4 day'
            $segment = $value.' '.$segment_name;

            // If the duration segment is anything other than 1, we need an 's'
            if ($value != 1) {
                $segment .= 's';
            }
            
            // Plop it into the array
            $array[] = $segment;
        }

        // Implode the array as a string, this way we get commas between each segment
        $timestring = implode(', ', $array);

        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I have been running for '.$timestring);
    }
    //===============================================================================================
    function setNick(&$irc, &$data)
    {
        global $bot;
        global $config;
        $newnick = $data->messageex[1];
        
        // need a valid channel for verify()
        $data->channel = $config['main_channel'];
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
        
        // need a valid channel for verify()
        $data->channel = $channel;
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message(SMARTIRC_TYPE_NOTICE,$channel,trim($message));
        }
    }
}
?>