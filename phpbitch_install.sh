#!/bin/bash

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
     FILENAME=`mktemp -d /tmp/$0.XXXXXX` || exit 1
     cd $FILENAME
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
else
     echo "Skipping MySQL Setup!"
fi
