<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_brain
{
    var $name = 'brain';
    var $description = 'this module allows for adding, deleting, and searching from bots "brain".';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid1;
    var $actionid2;
    var $actionid3;
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!search', $this, 'search_db');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!learn', $this, 'learn');
        $this->actionid3 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!forget', $this, 'forget');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
        $irc->unregisterActionid($this->actionid3);
    }
    
    //===============================================================================================
    function search_db(&$irc, &$data)
    {
        global $config;
        global $bot;
        $requester = $data->nick;
        $search = $data->messageex[1];
        $lowersearch = strtolower($data->messageex[1]);

        if (!empty($search)) {
            $query = "SELECT response FROM brain WHERE query='".$lowersearch."'";
            $bot->dbquery("UPDATE brain SET count = count + 1 WHERE query='".$lowersearch."'");
            $result = $bot->dbquery($query);
            $numrows = mysql_num_rows($result);
            if ($numrows > 0) {
                $row = mysql_fetch_array($result);
                $response = $row[0];
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': ['.$search.'] '.$response);
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I know nothing about '.$search);
            }
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': Please enter something to search for.');
        }
    }
    //===============================================================================================
    function learn(&$irc, &$data)
    {
        global $config;
        global $bot;
        $usersquery = $data->messageex[1];
        //$response = $data->messageex[2];
        
        // Get the response
        $temp=explode(' ',$data->message);
        $response='';
        for ($i=2;$i<count($temp);$i++)
            $response.=' '.$temp[$i];
            
         $response=trim($response);
         
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "INSERT INTO brain( `query`,`response`,`count`) VALUES('".$usersquery."','".$response."','0')";
                $res = $bot->dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Added '.$usersquery.' as '.$response);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error adding: '.mysql_error());
                }
            }
        }
    }
    //===============================================================================================
    function forget(&$irc, &$data)
    {
        global $config;
        global $bot;
        $usersquery = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && (get_level($data->nick) == USER_LEVEL_MASTER)) {
                $query = "DELETE FROM brain WHERE query='".$usersquery."'";
                $res = dbquery($query);

                if ($res) {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I will now plead to the 5th about '.$usersquery);
                } else {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Error removing: '.mysql_error());
                }
            }
        }
    }
}
?>