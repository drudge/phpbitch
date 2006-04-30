<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2003 Mirco 'meebey' Bauer <meebey@php.net>
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

 
class Net_SmartIRC_module_versions
{
    var $name = 'versions';
    var $version = '$Revision$';
    var $description = 'this module shows the version numbers of different things.';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!version', $this, 'show_phpbitchversion');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!moduleversions', $this, 'show_moduleversions');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!moduleversion', $this, 'show_moduleversion');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!phpversion', $this, 'show_phpversion');
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!smartircversion', $this, 'show_smartircversion');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function show_phpversion(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message($data->type, $data->channel, phpversion());
        }
    }
    
    function show_smartircversion(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message($data->type, $data->channel, SMARTIRC_VERSION);
        }
    }
    
    function show_phpbitchversion(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $irc->message($data->type, $data->channel, 'main: '.PHPBITCH_VERSION);
        }
    }
    
    function show_moduleversion(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            if (!isset($data->messageex[1])) {
                return;
            }
            
            $modulename = $data->messageex[1];
            if (isset($irc->_modules[$modulename])) {
                $module = &$irc->_modules[$modulename];
                $output = $module->name.': '.$module->version;
                
                $irc->message($data->type, $data->channel, $output);
            } else {
                $irc->message($data->type, $data->channel, $data->nick.': Sorry, I don\'t have the module "'.$modulename.'" loaded.');
            }
        }
    }

    function show_moduleversions(&$irc, &$data)
    {
        global $bot;
        
        if ($bot->isAuthorized($irc, $data->nick, $data->channel, USER_LEVEL_MASTER)) {
            $output = '';
            foreach ($irc->_modules as $key => $value) {
                $output .= $key.': '.$value->version.' | ';
            }
            
            $irc->message($data->type, $data->channel, $output);
        }
    }
}
?>