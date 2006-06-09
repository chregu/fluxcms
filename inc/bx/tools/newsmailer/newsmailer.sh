#/bin/bash

cd fluxcms

php inc/bx/tools/newsmailer/newsmailer.php preparemails
php inc/bx/tools/newsmailer/newsmailer.php sendmails
