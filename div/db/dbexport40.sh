#!/bin/bash
mysqldump --extended-insert=false  -Q --add-drop-table --complete-insert bxcms >  bxcms.sql

# Uncomment this to use sed for replacing DEFAULT CHARSET entries
#sed "s/ DEFAULT CHARSET=latin1//g" ./berghilfe.sql > berghilfe.sql.sed
#if [ -f "berghilfe.sql.sed" ];then
#    mv berghilfe.sql.sed berghilfe.sql
#fi
