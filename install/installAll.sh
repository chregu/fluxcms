TIMESPAN=600
MAXINST=10
NEWHOSTS=`mysql -N -B freeflux_master -e "select count(id) from master where active = 0"`
if test $NEWHOSTS -gt 0;
    then
    
    LASTCOUNT=`mysql -N -B  freeflux_master -e "select count(id) from master where installed > now() - $TIMESPAN;"`
    if test $LASTCOUNT -lt $MAXINST;
        then
        date >> justInstalled.dat
        echo "select id, host, email from master where active = 0 order by id limit 5"     |mysql -N freeflux_master >> justInstalled.dat
        for i in `echo "select id from master where active = 0 order by id limit 5"     |mysql -N freeflux_master` ; 
            do echo $i; 
            php index-shared.php $i 1   >> ./install.log
        done
        
        echo "***"
        echo "Installed Accounts:"
        tail -n 6 justInstalled.dat
        
        echo "***"
        echo -n "Total: "
        echo "select count(id) from master where active = 1" | mysql -N freeflux_master
        echo "***"
        echo -n "In Queue: "
        echo "select count(id) from master where active = 0" | mysql -N freeflux_master
        echo "***"
    else
        
        echo "$MAXINST or more installations in the last $TIMESPAN seconds";
    fi
else
    echo "No new registrations";
fi
