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
 
class Net_SmartIRC_module_temp
{
    var $name = 'temp';
    var $version = '$Revision$';
    var $description = 'this module will search a word from dictionary.com.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!temp', $this, 'temp');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================
    function temp(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        $requester = $data->nick;
        $type = $data->messageex[1];
        $convert = $data->messageex[2];
        
        if(is_numeric($convert)) {
            if ( $type == 'c' ) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$convert.'°C is '.round(((9/5)*$convert)+32,2).'°F.');
            } else if ( $type == 'f' ) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$convert.'°F is '.round((5/9)*($convert-32),2).'°C.');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I dunno how to handle '.$type.'.');
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I dunno how to handle '.$convert.'.');
        }
    }
}
?>