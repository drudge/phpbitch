<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_dictionary
{
    var $name = 'dictionary';
    var $description = 'this module will search a word from dictionary.com.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid;
    
    function module_init(&$irc)
    {
        $this->actionid = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dict', $this, 'dict');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid);
    }
    
    //===============================================================================================
    function dict(&$irc, &$data)
    {
        $search = $data->messageex[1];
        $word = $search;
        $word = str_replace(" ","%20",$word);
        $wordpage = implode ('', file ("http://www.dictionary.com/search?q=".$word));
        $begword = strpos($wordpage,"<a name=\"1\">");
        if ($begword != 0) {
           $wordpage = substr($wordpage,$begword,strlen($wordpage));
           $lastword = strpos($wordpage,"<table border=\"0\"")-13;
           $wordpage = substr($wordpage,0,$lastword+8);
           $wordpage = str_replace("<A TITLE=\"Click for guide to symbols.\" onClick=\"ahdpop();return false;\" HREF=\"/help/ahd4/pronkey.html\" CLASS=\"linksrc\"><b>Pronunciation Key</b></A>","",$wordpage);
           $wordpage = str_replace("/search?q=","getworddemo.php?word=",$wordpage);  // changes the referals in the text fetched to go to my page instead of dictionary.com's
     
           $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'Search results for \''.$search.'\'');
           $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, $wordpage);
        } else {
           $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, echo "No word found.");
        |
}

    }
}
?>