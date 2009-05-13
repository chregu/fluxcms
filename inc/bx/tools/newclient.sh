#!/bin/bash
# +----------------------------------------------------------------------+
# | newclient.sh                                                         |
# +----------------------------------------------------------------------+
# | Copyright (c) 2001,2002,2003,2004 Liip AG                            |
# +----------------------------------------------------------------------+
# | This program is free software; you can redistribute it and/or        |
# | modify it under the terms of the GNU General Public License (GPL)    |
# | as published by the Free Software Foundation; either version 2       |
# | of the License, or (at your option) any later version.               |
# | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
# +----------------------------------------------------------------------+
# | Author: Philipp Stucki <philipp@liip.ch>                             |
# +----------------------------------------------------------------------+
#
# $Id: index.php 6483 2005-12-02 14:56:52Z philipp $

svncmd=`which svn`
wgetcmd=`which wget`
#svncmd="echo svn"   # uncomment this line for debugging

BRANCH_STABLE="branches/1_5"
BRANCH_STABLE="trunk"
BARNCH_TRUNK="trunk"

SVN_EXTERNALS_LOCALINC="localinc        https://svn.liip.ch/repos/liip/localinc/trunk
"

PROJECT_NAME="$1"
TARGET_URL=https://svn.liip.ch/repos/liip/clients/
PWD=`pwd`

# -- a few functions ----
erroranddie() {
    echo -e "\n\033[1m\033[31m$ERROR\033[1m\033[0m\n"
    exit $?
}

message() {
    echo -e "\n\033[1m$MESSAGE\033[0m"
}

printok() {
    echo -en "\033[32mOK\033[0m"
}    
prompt() {
    echo -en "\033[35m$PROMPT\033[0m"
}

echo -e "newclient.sh\n"
echo "This script will create a Flux CMS skeleton for a new client project in the current directory"
echo

# -- read branch version ----

BRANCH=$BRANCH_STABLE
PROMPT="SVN Branch to be used (enter 'trunk' here to use trunk) "
prompt
echo -n "[$BRANCH] : "
read readval
if [ ! -z "$readval" ]; then
    BRANCH=$readval
fi

SVN_EXTERNALS="inc             https://svn.liip.ch/repos/public/fluxcms/$BRANCH/inc
webinc          https://svn.liip.ch/repos/public/fluxcms/$BRANCH/webinc
admin           https://svn.liip.ch/repos/public/fluxcms/$BRANCH/admin
install         https://svn.liip.ch/repos/public/fluxcms/$BRANCH/install
"

SVN_EXTERNALS_THEMES="standard https://svn.liip.ch/repos/public/fluxcms/$BRANCH/themes/standard
"

SVN_IGNORE="tmp
dynimages
"

# -- read project name ----
PROMPT="Project Name "
prompt
echo -n "[$PROJECT_NAME] : "
read readval
if [ ! -z "$readval" ]; then
    PROJECT_NAME=$readval
fi
if [ -z "$PROJECT_NAME" ]; then
    ERROR="I'm sorry but I can't continue unless you have given me a real project name."
    erroranddie
fi

if [ -d "$PWD/$PROJECT_NAME" ]; then
    ERROR="It looks like there is already a directory with the name '$PROJECT_NAME'.\nMay I ask you to remove it because I would be unable to successfully finish my work 
otherwise."
    erroranddie
fi

#PROMPT="Do you want me to add 'localinc' to the list of svn:externals? "
#prompt
#echo -n "[Y/n] : "
#read addlocalinc
#if [ `echo $addlocalinc| grep -i 'n'` ]; then
#echo -n ""
#else
#SVN_EXTERNALS="$SVN_EXTERNALS$SVN_EXTERNALS_LOCALINC"
#fi

TARGET_URL=$TARGET_URL$PROJECT_NAME

# -- read target repository ----
PROMPT="Target URL "
prompt
echo -n "[$TARGET_URL] : "
read readval
if [ ! -z "$readval" ]; then
    TARGET_URL=$readval
fi


echo -e "Settings Overview"
echo "-----------------"

MESSAGE="Project Name"
message
echo "$PROJECT_NAME"

MESSAGE="Project Directory"
message
echo "$PWD/$PROJECT_NAME"

MESSAGE="SVN Remote Directory"
message
echo "$TARGET_URL"

MESSAGE="SVN Externals"
message
echo "$SVN_EXTERNALS"

MESSAGE="SVN Ignore"
message
echo "$SVN_IGNORE"

echo -e "\nPress <Enter> to continue or <Ctrl>+<C> to abort."
read

mkdir $PROJECT_NAME

$svncmd import -m "\"Initial import of $PROJECT_NAME by newclient.sh\"" $PROJECT_NAME $TARGET_URL
#rm -rf $PROJECT_NAME
$svncmd co $TARGET_URL $PROJECT_NAME
echo  "$SVN_EXTERNALS" > ._svnexternals
$svncmd propset "svn:externals" -F ._svnexternals $PROJECT_NAME
rm ._svnexternals
echo  "$SVN_IGNORE" > ._svnignore
$svncmd propset "svn:ignore" -F ._svnignore $PROJECT_NAME
rm ._svnignore

$svncmd ci -m "\"Default properties added by newclient.sh\"" $PROJECT_NAME
$wgetcmd -O $PROJECT_NAME/index.php http://svn.liip.ch/repos/public/fluxcms_demo/$BRANCH/index.php
chmod 777 $PROJECT_NAME

mkdir -p $PROJECT_NAME/div/db
$wgetcmd -O $PROJECT_NAME/div/db/bxcms.sql http://svn.liip.ch/repos/public/fluxcms_demo/$BRANCH/div/db/bxcms.sql

# add forms and dbforms2 directory necessary for some admin forms
$svncmd export https://svn.liip.ch/repos/public/fluxcms_demo/$BRANCH/forms $PROJECT_NAME/forms
$svncmd add $PROJECT_NAME/forms

$svncmd export https://svn.liip.ch/repos/public/fluxcms_demo/$BRANCH/dbforms2 $PROJECT_NAME/dbforms2
$svncmd add $PROJECT_NAME/dbforms2

$svncmd ci -m "\"forms and dbforms2 folder add by newclient.sh\"" $PROJECT_NAME/forms $PROJECT_NAME/dbforms2

$svncmd export https://svn.liip.ch/repos/public/fluxcms_demo/$BRANCH/themes/3-cols/ $PROJECT_NAME/themes/$PROJECT_NAME

$svncmd add $PROJECT_NAME/themes
$svncmd ci -m "\"project theme added by newclient.sh\"" $PROJECT_NAME/themes

echo  "$SVN_EXTERNALS_THEMES" > ._svnexternals
$svncmd propset "svn:externals" -F ._svnexternals $PROJECT_NAME/themes
rm ._svnexternals

$svncmd up $PROJECT_NAME

MESSAGE="The skeleton for '$PROJECT_NAME' has been successfully generated. You can now continue the installation \nusing the web-based installer."
message

echo -e "\nDon't forget to:"
echo "  - change the name of the theme to '$PROJECT_NAME' in conf/config.xml"
echo "  - add all directories created by the installer to the SVN repository"
echo -e "\nBye."
