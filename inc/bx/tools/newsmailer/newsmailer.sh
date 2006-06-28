#/bin/bash

cd /www-data/kunden/scansystems/test/

php inc/bx/tools/newsmailer/newsmailer.php preparemails
php inc/bx/tools/newsmailer/newsmailer.php sendmails
