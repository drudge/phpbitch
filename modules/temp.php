<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_temp
{
    var $name = 'temp';
    var $description = 'this module will search a word from dictionary.com.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid;
    
    function module_init(&$irc)
    {
        $this->actionid = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!temp', $this, 'temp');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid);
    }
    
    //===============================================================================================
    function temp(&$irc, &$data)
    {
        global $config;
        global $bot;
        $requester = $data->nick;
        $type = $data->messageex[1];
        $convert = $data->messageex[2];

        if ( $type == 'c' ) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$convert.'°C is '.round(((9/5)*$convert)+32,2).'°F.');
        } else if ( $type == 'f' ) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': '.$convert.'°F is '.round((5/9)*($convert-32),2).'°C.');
        } else {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $requester.': I dunno how to handle '.$type.'.');
        }
    }
}
?>