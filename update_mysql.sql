# $Id$
# $Revision$
# $Author$
# $Date$
#
# phpbitch - an IRC bot written in PHP which is based on SmartIRC class
#
# Copyright (c) 2003 Mirco 'meebey' Bauer <mail@meebey.net>
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
###########################################################################
# Changes to MySQL for my latest CVS commit.

#
# Table structure for table `users_levels`
#

CREATE TABLE users_levels (
  user varchar(16) NOT NULL default '',
  channel varchar(32) NOT NULL default '',
  level int(11) NOT NULL default '0',
  PRIMARY KEY  (user,channel)
) TYPE=MyISAM;

#
# Dumping data for table `users_levels`
#

INSERT INTO users_levels (user, channel, level) VALUES ('meebey', '*', 4);
INSERT INTO users_levels (user, channel, level) VALUES ('phpbitch', '*', 5);
INSERT INTO users_levels (user, channel, level) VALUES ('gtkbitch', '*', 5);
INSERT INTO users_levels (user, channel, level) VALUES ('sqlbitch', '*', 5);
INSERT INTO users_levels (user, channel, level) VALUES ('codebitch', '*', 5);
INSERT INTO users_levels (user, channel, level) VALUES ('bthbitch', '*', 5);
INSERT INTO users_levels (user, channel, level) VALUES ('DrUDgE', '*', 4);


# now some shit we gotta take care of..
ALTER TABLE `brain` DROP INDEX `query` , ADD PRIMARY KEY ( `query` );
ALTER TABLE `users` DROP `level`;
