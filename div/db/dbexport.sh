#!/bin/bash
mysqldump   --extended-insert=false  -Q --add-drop-table --complete-insert bxcms >  bxcms.sql

