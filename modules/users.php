<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_users
{
    var $name = 'users';
    var $description = 'this module has commands to add/remove/search bots user database.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid1;
    var $actionid2;
    var $actionid3;
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!who ', $this, 'who');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!adduser', $this, 'adduser');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!deluser', $this, 'deluser');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
        $irc->unregisterActionid($this->actionid3);
    }
    //===============================================================================================    
    function who(&$irc, &$data)
    {
        global $config;
        global $bot;
        $requester = $data->nick;
        $lookupfor = $data->messageex[1];
        $lowerlookupfor = strtolower($data->messageex[1]);

        if (isset($irc->channel[$data->channel]->users[$lowerlookupfor])) {
            $host = $irc->channel[$data->channel]->users[$lowerlookupfor]->host;
            $ident = $irc->channel[$data->channel]->users[$lowerlookupfor]->ident;
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is not in this channel');
            return;
        }

        $result = $bot->reverseverify($irc, $host, $lookupfor, $ident);

        if ($result !== false) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$lookupfor.' is '.$result.'['.gethostbyname($host).']');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $lookupfor.' is not a registered user of '.$data->channel.'.');
        }
    }
        
    //===============================================================================================
    function adduser(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];
        $host = $data->messageex[2];
        $level = $data->messageex[3];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        $result = $bot->reverseverify($irc, $data->host, $data->nick, $data->ident);

        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
            $query = "INSERT INTO dnsentries( `nickname`,`dnsalias`,`level`) VALUES('".$nick."','".$host."','".$level."')";
            $res = $bot->dbquery($query);

            if ($res) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$nick.' as '.$host.' with a level of '.$level);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
            }
        }
    }
    //===============================================================================================
    function deluser(&$irc, &$data)
    {
        global $bot;
        $nick = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        $result = $bot->reverseverify($irc, $data->host, $data->nick, $data->ident);

        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
            $query = "DELETE FROM dnsentries WHERE nickname='".$nick."'";
            $res = $bot->dbquery($query);

            if ($res) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Deleted '.$nick.' from registered users database.');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
            }
        }
    }
}
?>