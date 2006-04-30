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
class Net_SmartIRC_module_trac
{
    var $name = 'trac';
    var $version = '$Revision$';
    var $description = 'This module will return information about tickets, changesets and roadmaps of an installed trac';
    var $author = 'Amir Mohammad Saied <amir@php.net>';
    var $license = 'GPL';

    var $actionids = array();
    // Base URL of an installed trac with trailing /
    var $baseurl = 'http://dev.jaws-project.com/cgi-bin/trac.cgi/';

    function module_init(&$irc)
    {
        $this->actionids = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!roadmap', $this, 'roadmap');
        $this->actionids = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ticket', $this, 'ticket');
        $this->actionids = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!changeset', $this, 'changeset');
    }

    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }

    function roadmap(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }

        if(isset($data->messageex[1])) {
            $roadmap_url = $this->baseurl.'milestone/'.$data->messageex[1];
            $req =& new HTTP_Request($roadmap_url);
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->sendRequest();
            if (!$req->getResponseBody()) {
                $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to the trac server failed!');
            } else {
                $total_roadmap = $req->getResponseBody();
                ereg("<title>(.*)</title>", $total_roadmap, $reg);
                if (stristr($reg[1], "Invalid")) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Invalid Milestone Name');
                } else {
                    ereg("<p class=\"percent\">(.*)%</p>", $total_roadmap, $reg);
                    $completion = $reg[1];
                    $string = "<dd><a href=\"(/cgi-bin/trac.cgi)?/query\?status=closed&amp;milestone=".$data->messageex[1]."\">([0-9]+)</a></dd>";
                    ereg($string, $total_roadmap, $reg);
                    $closed = $reg[2];
                    $string = "<dd><a href=\"(/cgi-bin/trac.cgi)?/query\?status=new&amp;status=assigned&amp;status=reopened&amp;milestone=".$data->messageex[1]."\">([0-9]+)</a></dd>";
                    ereg($string, $total_roadmap, $reg);
                    $active = $reg[2];
                    $final_output = 'Complete: '.$completion.'% | Active: '.$active.' | Closed: '.$closed;
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $final_output);
                }
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'usage: !roadmap <number>');
        }
    }

    function changeset(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }

        if (isset($data->messageex[1])) {
            $changeset_url = $this->baseurl.'changeset/'.$data->messageex[1];
            $req =& new HTTP_Request($changeset_url);
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->sendRequest();
            if (!$req->getResponseBody()) {
                $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to the trac server failed!');
            } else {
                $total_changeset = $req->getResponseBody();
                ereg("<title>(.*)</title>", $total_changeset, $reg);
                if (stristr($reg[1], "Error")) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Invalid Changeset');
                } else {
                    $final_output = array();
                    $string = "<dd class=\"time\">([0-9]{2}/[0-9]{2}/[0-9]{2,4}.?[0-9]{2}:[0-9]{2}:[0-9]{2}.?([AP]M)?)</dd>";
                    ereg($string, $total_changeset, $chng_reg);
                    $timestamp = $chng_reg[1];
                    $string = "<dd class=\"author\">([A-Za-z0-9_]+)</dd>";
                    ereg($string, $total_changeset, $chng_reg);
                    $author = $chng_reg[1];
                    $string = "<dd class=\"message\" id=\"searchable\">(.*)<dt class=\"files\">";
                    ereg($string, $total_changeset, $chng_reg);
                    $changeset_desc = trim(strip_tags(implode(explode("\n", $chng_reg[1]))));
                    $final_output[] = 'Timestamp: '.$timestamp.' | Author:'.$author;       
                    $final_output[] = 'Message: '.($changeset_desc);
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $final_output);
                }
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'usage: !changeset <number>');
        }
    }

    function ticket(&$irc, &$data)
    {
        global $bot;
        if(!$bot->isMastah($irc, $data)) {
            return;
        }

        if(isset($data->messageex[1])) {
            $ticket_url = $this->baseurl.'ticket/'.$data->messageex[1];
            $req =& new HTTP_Request($ticket_url);
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->sendRequest();
            if (!$req->getResponseBody()) {
                $irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to the trac server failed!');
            } else {
                $total_ticket = $req->getResponseBody();
                ereg("<title>(.*)</title>", $total_ticket, $reg);
                if (stristr($reg[1], "Invalid")) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Invalid Ticket Number');
                } else {
                    ereg("<div class=\"description\">(.*)<h2>Attachments</h2>", $total_ticket, $reg);
                    $ticket_desc = trim(strip_tags(implode(explode("\n", $reg[1]))));
                    ereg("<h3 class=\"status\">Status: <strong>(.*)</strong></h3>", $total_ticket, $reg);
                    $ticket_status = 'Status: '.$reg[1];
                    $final_output = array();
                    $final_output[] = $ticket_status;
                    $final_output[] = $ticket_desc;
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $final_output);
                }
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'usage: !ticket <number>');
        }
    }
}
?>
