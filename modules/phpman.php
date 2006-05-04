<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2006 Amir Mohammad Saied <amir@php.net>
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
class Net_SmartIRC_module_phpman
{
    var $name = 'phpman';
    var $revision = '$Revision$';
    var $description = 'This module will return informations about php functions';
    var $author = 'Amir Mohammad Saied';
    var $license = 'GPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!php', $this, 'php');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionhandler($value);
        }
    }
    
    function php(&$irc, &$data)
    {
        if (isset($data->messageex[1])) {
            $url = 'http://ir.php.net/'.$data->messageex[1];
            $req =& new HTTP_Request($url);
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->sendRequest();
            if (!$req->getResponseBody()) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to the php.net failed!');
            } else {
                $funcman = $req->getResponseBody();
                ereg("CLASS=\"refnamediv\"(.*)CLASS=\"refsect1\"", $funcman, $desc);
                ereg(">(.*)</P", $desc[1], $reg);
                $versions = trim(strip_tags($reg[1]));
                ereg(">".strtolower($data->messageex[1])."&nbsp;--&nbsp;(.*)</DIV", $desc[1], $reg);
                $brief_desc = trim(html_entity_decode(strip_tags($reg[1])));
                $final_output = array();
                $final_output[] = $versions;
                $final_output[] = $data->messageex[1].": ".$brief_desc;
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $final_output);
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'usage: !php <number>');
        }
    }
}
?>
