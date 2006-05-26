#!/bin/bash
mysqldump  --compatible=mysql40 --skip-opt --extended-insert=false  -Q --add-drop-table --complete-insert bxcms >  bxcms.sql

