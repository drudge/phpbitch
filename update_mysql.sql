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
# This file when ran like: 
# mysql -u user -p database < update_mysql.sql
# will make older phpbitch databases current, make sure
# to 
# Change the tables name, it was ugly before and annoyed me.
ALTER TABLE `dnsentries` RENAME `users`;

# Same ugly field, lets keep it real brothers.
ALTER TABLE `users` CHANGE `dnsalias` `host` VARCHAR( 64 ) NOT NULL;
