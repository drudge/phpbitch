
# $Id$
# $Revision$
# $Author$
# $Date$
#
# Copyright (c) 2003 Nicholas 'DrUDgE' Penree <drudge@x-php.net>
#
# Full GPL License: <http://www.gnu.org/licenses/gpl.txt>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA

# Table structure for table `answering_machine`
DROP TABLE IF EXISTS `answering_machine`;
CREATE TABLE `answering_machine` (
  `to_nick` varchar(9) NOT NULL default '',
  `from_nick` varchar(9) NOT NULL default '',
  `subject` varchar(80) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text
) TYPE=MyISAM;

# Table structure for table `brain`
DROP TABLE IF EXISTS `brain`;
CREATE TABLE `brain` (
  `query` varchar(100) NOT NULL default '',
  `response` tinytext NOT NULL,
  `count` int(11) default '0',
  UNIQUE KEY `query` (`query`)
) TYPE=MyISAM;

# Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `nickname` varchar(32) NOT NULL default '',
  `ident` varchar(32) NOT NULL default '',
  `host` varchar(64) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`nickname`,`host`)
) TYPE=MyISAM;
