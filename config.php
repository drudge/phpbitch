<?php
/**
 * $Id$
 * $Revision$
 * $Author$
 * $Date$
 */

$config = array();

/* database settings */
$db_type = 'mysql';
$db_server = 'localhost';
$db_username = 'phpbitch';
$db_password = 'php';
$db_db = 'phpbitch';
$config['db_dsn'] = "$db_type://$db_username:$db_password@$db_server/$db_db";

/* general settings */
$config['real_name'] = 'IRC service bot';
$config['channels'] = array('#php-gtk', '#linux-help', '#kde', '#php++');
$config['nick'] = 'phpbitch';
$config['ident'] = 'phpbitch';
$config['alt_nick'] = 'phpb1tch';

/* bot specific */
$config['version'] = 'phpbitch 1.0';
$config['modules_path'] = dirname(__FILE__).'/modules';
$config['answer_questions'] = false;

/* irc server info */
$config['irc_server']="irc.stealth.net";
$config['irc_port']="5555";

$config['send_delay'] = 500;

// friends go here, the order is the priority for mastermode
$config['friend_bots'] = array('gtkbitch', 'phpbitch', 'codebitch', 'sqlbitch', 'bthbitch');
?>