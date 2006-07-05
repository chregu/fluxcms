#/bin/bash

cd /var/www/kolibri-seminare.ch/

/usr/local/bin/php inc/bx/tools/newsmailer/newsmailer.php preparemails
/usr/local/bin/php inc/bx/tools/newsmailer/newsmailer.php sendmails
