<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_mastah
{
    var $name = 'mastah';
    var $description = 'this module will return if the bot is the current mastah or not.';
    var $author = 'Mirco \'meebey\' Bauer <meebey@php.net>';
    var $license = 'GPL';
    var $actionid;
    
    function module_init(&$irc)
    {
        $this->actionid = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!mastah', $this, 'mastah');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid);
    }
    
    function mastah(&$irc, &$data)
    {
        global $bot;
    
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data->host, $data->nick, $data->ident);
        
        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
            if ($bot->isMastah($irc, $data)) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am mastah!');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am not mastah');
            }
        }
    }
}
?>