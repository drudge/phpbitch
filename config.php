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
$config['real_name'] = 'phpbitch: yes, its that time of the month.';
$config['main_channel'] = array('#pear');
$config['channels'] = array('#phpbitch', '#linux-help', '#php-gtk', '#kde', '#xhtml', '#!/bin/sh', '#php++', '#smartirc', '#gnome', '#phpbb', '#mekkablue', '#rush');
$config['nick'] = 'phpbitch';
$config['ident'] = 'phpbitch';
$config['alt_nick'] = 'phpb1tch';

/* bot specific */
$config['version'] = 'phpbitch 1.0';
$config['modules_path'] = dirname(__FILE__).'/modules';
$config['answer_questions'] = false;

/* irc server info */
$config['irc_server'] = "irc.stealth.net";
$config['irc_port'] = 5555;

$config['send_delay'] = 500;

// friends go here, the order is the priority for mastermode
$config['friend_bots'] = array('gtkbitch', 'meebitch', 'phpbitch', 'codebitch', 'sqlbitch', 'helpbitch');

$config['emergency_users']= array('drudge'=>'6e8fe3e9e2816e6a93c449b591582540',
                                  'meebey'=>'2937a4503f726bcf018b91d2e8a669dc',
                                  'pluesch0r'=>'edc8540bed09f428ab2d556d562c422c');
?>