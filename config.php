<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */

/* database settings */

$mysql_server = 'localhost';
$mysql_username = 'gtkbitch';
$mysql_password = 'php';
$mysql_db = 'gtkbitch';

/* general settings */

$config = array();

$config['real_name'] = 'IRC service bot for #php-gtk';
$config['channels'] = array('#php-cafe','#php-gtk');
$config['nick'] = 'phpbitch';
$config['ident'] = 'phpbitch';
$config['alt_nick'] = 'phpb1tch';

/* bot specific */
$config['version'] = 'phpbitch 1.0 | portions by TiX';
$config['modules_path'] = '/home/drudge/phpbitch/repository/phpbitch/modules';
$config['answer_questions'] = true;

/* irc server info */
$config['irc_server']="irc.stealth.net";
$config['irc_port']="5555";

// friends go here, the order is the priority for mastermode
$config['friend_bots'] = array('phpbitch','gtkbitch');
?>