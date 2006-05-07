<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 *
 * Copyright (c) 2006 Amir Mohammad Saied <amir@php.net>
 */

require_once 'HTTP/Request.php';
class Net_SmartIRC_module_pearbugs
{
    var $name = 'pearbugs';
    var $description = 'Will return PEAR bugs info';
    var $author = 'Amir Mohammad Saied <amir@php.net>';
    var $version = '$Revision$';
    var $license = 'LGPL';
    
    var $actionids = array();
    
    function module_init(&$irc)
    {
        $this->actionids[] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!bug', $this, 'bug');
    }
    
    function module_exit(&$irc)
    {
        foreach ($this->actionids as $value) {
            $irc->unregisterActionid($value);
        }
    }
    
    function bug(&$irc, &$data)
    {
        if (isset($data->messageex[1])) {
            $bugurl = 'http://pear.php.net/bugs/bug.php?id='.(int)$data->messageex[1];
            $req =& new HTTP_Request($bugurl);
            $req->setMethod(HTTP_REQUEST_METHOD_GET);
            $req->sendRequest();
            if (!$req->getResponseBody()) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Connection to PEAR server failed!');
            } else {
                $totalbug = $req->getResponseBody();
                ereg("<title>(.*)</title>", $totalbug, $reg);
                if ($reg[1] == 'PEAR :: No Such Bug') {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Invalid bug Number');
                } else {
                    ereg("<div id=\"bugheader\">(.*)<div id=\"controls\">", $totalbug, $reg);
                    ereg("<th class=\"details\" id=\"number\">(Bug|Request)(.*)#([0-9]+)</th>", $reg[1], $buginfo);
                    $type = $buginfo[1];
                    $number = $buginfo[3];
                    ereg("<td (colspan=\"3\"|style=\"white-space: nowrap;\")>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2} UTC)</td>", $reg[1], $buginfo);
                    $timestamp = $buginfo[2];
                    ereg("<td>(Open|Closed|Duplicate|Critical|Assigned|Analyzed|Verified|Suspended|Wont fix|No feedback|Feedback|Bogus)</td>", $reg[1], $buginfo);
                    $status = $buginfo[1];
                    ereg("<td id=\"summary\" colspan=\"3\">(.*)<tr id=\"submission\">", $reg[1], $buginfo);
                    $desc = trim(str_replace("\n", "", strip_tags($buginfo[1])));
                    ereg("<th class=\"details\">Package:</th>(.*)<tr id=\"situation\">", $reg[1], $buginfo);
                    $package = trim(str_replace("\n", "", strip_tags($buginfo[1])));
                    $final_output = array();
                    $final_output[] = $type.' #'.$number.' (Status: '.$status.', Package: '.$package.') ['.$timestamp.']';
                    $final_output[] = $desc;
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $final_output);
                }
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'usage: !bug <bug_number>');
        }
    }
}
?>
