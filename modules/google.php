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

require_once 'HTTP/Request.php';
class Net_SmartIRC_module_google
{
    var $name = 'google';
    var $version = '$Revision$';
    var $description = 'this module will search and return the first 4 links of a google search.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $this, 'google');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    //===============================================================================================
    function google(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }
        
        // Get the search
        $search='';
        for ($i = 1;$i < count($data->messageex); $i++) {
            $search .='+'.$data->messageex[$i];
        }
        
        $question=trim($search,'+');
        if (!$question) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'No search string given.');
        } else {
            $req =& new HTTP_Request( );
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->setURL('http://www.google.com/search?as_q='.$search.'&num=5');
            $req->sendRequest();
            if (PEAR::isError($req->sendRequest())) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to google failed!');
            } else {
                $page = $req->getResponseBody();
                $ex1 = explode('<font color=#008000>', $page);
                for($i=0;$i<count($ex1);$i++) {
                    $ex2[] = explode(' - ', $ex1[$i]);
                }
                
                $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'Google Search Results for: \''.$question.'\'');
                
                if (count($ex2) >=5) {
                    $count = 5;
                } else {
                    $count = count($ex2);
                }
                
                if ($count > 0) {
                    for($i = 1; $i <= $count; $i++) {
                        $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'http://'.strip_tags($ex2[$i][0]));
                    }
                } else {
                    $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'No results for \''.$question.'\'.');
                }
            }
        }
    }
}
?>
