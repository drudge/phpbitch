<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_botcmds
{
    var $name = 'botcmds';
    var $description = 'this module has some bot commands.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid1;
    var $actionid2;
    
    function module_init(&$irc)
    {
        $this->actionid1 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!uptime$', $this, 'uptime');
        $this->actionid2 = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $this, 'setNick');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid1);
        $irc->unregisterActionid($this->actionid2);
    }
    
    //===============================================================================================       
    function uptime(&$irc,&$data)
    {
        global $config;
        global $start;
        $time=time()-$start;
        
        $weeks=$time/604800; 
        $days=($time%604800)/86400; 
        $hours=(($time%604800)%86400)/3600; 
        $minutes=((($time%604800)%86400)%3600)/60; 
        $seconds=(((($time%604800)%86400)%3600)%60); 
        
        if(round($days)) $timestring.=round($days).' day(s) '; 
        if(round($hours)) $timestring.=round($hours).' hour(s) '; 
        if(round($minutes)) $timestring.=round($minutes).' minute(s)'; 
        if(!round($minutes)&&!round($hours)&&!round($days)) $timestring.=round($seconds).' second(s)'; 

        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'I have been running for '.$timestring);
    }
    //===============================================================================================
    function setNick(&$irc, &$data)
    {
        global $config;
        global $bot;
        $newnick = $data->messageex[1];

        // don't verify ourself
        if (strpos($data->nick, $irc->_nick) !== false) {
            return;
        }

        if ($data->channel == $data->channel) {
            $result = $bot->reverseverify($irc, $data->host, $data->nick);

            if ($result !== false && ($bot->get_level($data->nick) >= USER_LEVEL_MASTER)) {
                $irc->changeNick($newnick);
            }
        }
    }
}
?>