#!/bin/bash
mysqldump5   --extended-insert=false  -Q --add-drop-table --complete-insert fluxcms >  bxcms.sql

