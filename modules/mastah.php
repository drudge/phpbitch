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
    var $actionid1;
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_QUERY, '^!mastah', $this, 'mastah');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
    }
    
    function mastah(&$irc, &$data)
    {
        global $bot;
    
        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }
        
        $result = $bot->reverseverify($irc, $data->host, $data->nick, $data->ident);
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'ident: '.$data->ident);
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'result: '.$result);
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'level: '.$bot->get_level($data->nick));
        
        if ($result !== false && ($bot->get_level($data->nick) == USER_LEVEL_MASTER)) {
            if ($bot->isMastah($irc, $data)) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am not mastah');
            } else {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I am mastah!');
            }
        }
    }
}
?>