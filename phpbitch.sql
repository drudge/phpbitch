# phpMyAdmin MySQL-Dump
# version 2.5.0-rc1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Jul 15, 2003 at 07:16 PM
# Server version: 4.0.13
# PHP Version: 4.3.2
# Database : `phpbitch`
# --------------------------------------------------------

#
# Table structure for table `answering_machine`
#
# Creation: May 25, 2003 at 06:26 PM
# Last update: May 25, 2003 at 06:26 PM
#

DROP TABLE IF EXISTS `answering_machine`;
CREATE TABLE `answering_machine` (
  `to_nick` varchar(9) NOT NULL default '',
  `from_nick` varchar(9) NOT NULL default '',
  `subject` varchar(80) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `brain`
#
# Creation: Apr 25, 2003 at 03:51 PM
# Last update: Jul 15, 2003 at 05:16 PM
#

DROP TABLE IF EXISTS `brain`;
CREATE TABLE `brain` (
  `query` varchar(100) NOT NULL default '',
  `response` tinytext NOT NULL,
  `count` int(11) default '0',
  UNIQUE KEY `query` (`query`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `users`
#
# Creation: Jul 13, 2003 at 06:06 PM
# Last update: Jul 15, 2003 at 02:05 PM
#

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `nickname` varchar(32) NOT NULL default '',
  `ident` varchar(32) NOT NULL default '',
  `host` varchar(64) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`nickname`,`host`)
) TYPE=MyISAM;

