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
 
class Net_SmartIRC_module_hex_ip
{
    var $name = 'temp';
    var $description = 'This module will convert ip2hex and hex2ip.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!hex2ip', $this, 'hex2ip');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ip2hex', $this, 'ip2hex');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    //===============================================================================================
    function hex2ip(&$irc, &$data)
    {
        global $bot;
        
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $hex = $data->messageex[1];
        $shit = '';
    
        if (empty($hex)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': usage: !hex2ip <hex_value>');
            return;
        }
        
        for ($i = 0; $i < strlen($hex); $i+=2) {
            $ip .= hexdec(substr($hex,$i,2)).'.';
        }
        $ip = rtrim($ip, '.');
        
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$hex.' as an IP address is '.$ip);
    }
    //===============================================================================================
    function ip2hex(&$irc, &$data)
    {
        global $bot;
        
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        $requester = $data->nick;
        $ip = $data->messageex[1];
        $shit = '';
        
        if (empty($ip)) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': usage: !ip2hex <ip_address>');
            return;
        }
        $dump=explode('.',$ip);
        $hex = '';
        foreach ($dump as $part) {
            $hex .= dechex($part);
        }
 
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$ip.' in hexidecimal form is '.$hex);
    }
}
?>