#!/bin/bash
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

# Defaults
BITCH_VERSION="1.0pre"
DIST_ARCHIVE="phpbitch-$BITCH_VERSION.tar.gz"

clear
echo "           __          __    _ __       __   "
echo "    ____  / /_  ____  / /_  (_) /______/ /_  " 
echo "   / __ \/ __ \/ __ \/ __ \/ / __/ ___/ __ \ "
echo "  / /_/ / / / / /_/ / /_/ / / /_/ /__/ / / / "
echo " / .___/_/ /_/ .___/_.___/_/\__/\___/_/ /_/  "
echo "/_/         /_/    Version $BITCH_VERSION    "

read -p "Enter installation directory [~/phpbitch]: " INSTALL_DIR

eval INSTALL_DIR=${INSTALL_DIR:="~/phpbitch"}

# Make sure we can make the directory, or quit
mkdir $INSTALL_DIR &> /dev/null
cd $INSTALL_DIR; INSTALL_DIR=`pwd`; cd $OLDPWD
if [ $? -ne 0 ]
then
    echo "Exiting: Could not make $INSTALL_DIR"
    exit 1
fi

# Copy file so we can find it
cp $DIST_ARCHIVE $INSTALL_DIR/ &> /dev/null

cd $INSTALL_DIR

# CVS is bleeding edge phpbitch ;)
read -p "Download latest version from CVS repository? [y/N]: " GET_CVS
eval GET_CVS=${GET_CVS:="N"}

if [ "$GET_CVS" = "y" -o "$GET_CVS" = "Y" ]
then
     if [ `whereis cvs` -eq 0 ]; then echo "Exiting: Cannot find cvs!"; exit 1; fi
     
     FILENAME=`mktemp -d /tmp/$0.XXXXXX` || exit 1
     cd $FILENAME
     if [ -f ~/.cvspass ]; then
        FOUND=`grep anonymous@cvs.meebey.net ~/.cvspass|wc -l|cut --bytes=7-`
        if [ "$FOUND" != "1" ]; then
           echo "/1 :pserver:anonymous@cvs.meebey.net:2401/cvs A" >> ~/.cvspass
        fi
     else
        echo "/1 :pserver:anonymous@cvs.meebey.net:2401/cvs A" > ~/.cvspass
     fi
     cvs -d :pserver:anonymous@cvs.meebey.net:/cvs checkout phpbitch
     rm -Rf $INSTALL_DIR
     mv phpbitch $INSTALL_DIR
     cd $INSTALL_DIR
     rm -Rf $FILENAME
else
     if [ ! -e $DIST_ARCHIVE ]; then echo "Exiting: Cannot find $DIST_ARCHIVE!"; exit 1; fi
     tar -xzf $DIST_ARCHIVE
     rm $DIST_ARCHIVE
fi

read -p "Setup initial MySQL databases? [y/N]: " SETUP_MYSQL
eval SETUP_MYSQL=${SETUP_MYSQL:="N"}

if [ "$SETUP_MYSQL" = "y" -o "$SETUP_MYSQL" = "Y" ]
then
    MYSQL_FILE="$INSTALL_DIR/phpbitch.sql"
    if [ ! -e $MYSQL_FILE ]; then echo "Exiting: Cannot find $MYSQL_FILE!"; exit 1;fi 
    
    read -p "MySQL Username? [phpbitch]: " MYSQL_USER
    eval MYSQL_USER=${MYSQL_USER:="phpbitch"}
    
    read -p "MySQL Database? [phpbitch]: " MYSQL_DB
    eval MYSQL_DB=${MYSQL_DB:="phpbitch"}

    mysql -u $MYSQL_USER -p $MYSQL_DB < $MYSQL_FILE

    read -p "Setup Bot Master Entry? [Y/n]: " SETUP_MASTER
    eval SETUP_MASTER=${SETUP_MASTER:="Y"}

   if [ "$SETUP_MASTER" = "y" -o "$SETUP_MASTER" = "Y" ]
   then
        read -p "Nickname? [DaMastah]: " M_NICK
        eval M_NICK=${M_NICK:="DaMastah"}
    
        read -p "Ident? [~damastah]: " M_IDENT
        eval M_IDENT=${M_IDENT:="~damasta"}

        read -p "Host? [damastah.org]: " M_HOST
        eval M_HOST=${M_HOST:="damasta.org"}
    
    mysql -u $MYSQL_USER -p $MYSQL_DB -e "INSERT INTO users (nickname , ident , host , level ) VALUES ('$M_NICK', '$M_IDENT', '$M_HOST', '4');"
   else
        echo "Skipping Bot Master Setup!"
   fi
else
        echo "Skipping MySQL Setup!"
fi

