<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */
 
class Net_SmartIRC_module_google
{
    var $name = 'google';
    var $description = 'this module will search and return the first 4 links of a google search.';
    var $author = 'Nicholas \'DrUDgE\' Penree <drudge@php-coders.net>';
    var $license = 'GPL';
    
    var $actionid;
    
    function module_init(&$irc)
    {
        $this->actionid = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $this, 'google');
    }
    
    function module_exit(&$irc)
    {
        $irc->unregisterActionid($this->actionid);
    }
    
    function google(&$irc, &$data)
    {
        // Get the search
        $temp=explode(' ',$data->message);
        $search='';
        for ($i=1;$i<count($temp);$i++) {
            $search.='+'.$temp[$i];
        }
        
        $question=trim($search,'+');
        if (!$question) {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'No search string given.');
        } else {
            $fp = fsockopen('www.google.com', 80, $errno, $errstr, 30);
            if (!$fp) {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Socket connection failed: '.$errstr);
            } else {
                fputs($fp, "GET /search?as_q=$question&num=100 HTTP/1.0\r\nHost: www.google.com\r\n\r\n");
                $page = '';
                while (!feof($fp)) {
                    $raw = fgets($fp);
                    $page .= $raw;
                }
                
                $ex1 = explode('<font color=#008000>', $page);
                for($i=0;$i<count($ex1);$i++) {
                    $ex2[] = explode(' - ', $ex1[$i]);
                }
                
                $irc->setSenddelay(500);
                $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'Google Search Results for: \''.$question.'\'');
                
                if (count($ex2) >=5) {
                    $count=5;
                } else {
                    $count=count($ex2);
                }
                
                if ($count > 0) {
                    for($i=1;$i<$count;$i++) {
                        $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'http://'.$ex2[$i][0]);
                    }
                } else {
                    $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, 'No results for \''.$question.'\'.');
                }
                
                $irc->setSenddelay(250);
                fclose($fp);
            }
        }
    }
}
?>