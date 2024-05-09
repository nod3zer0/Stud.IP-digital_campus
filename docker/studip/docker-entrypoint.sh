#!/bin/bash
set -e

STUDIP='/var/www/studip'
CONFIGFILE="$STUDIP/config/config_local.inc.php"
DOCKERCONFIGFILE="/config/config_local.inc.php"
CONF="$STUDIP/config/config.inc.php"

# Check if we have a config
if [ ! -f $CONFIGFILE ]; then
    echo "Setting up new config"
    cp "$DOCKERCONFIGFILE" "$CONFIGFILE"
    cp "$CONF.dist" "$CONF"
fi


# wait until MySQL is really available
maxcounter=45

counter=1
while ! mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        echo "We have been waiting for MySQL too long already; failing." >&2
        exit 1
    fi;
done

sh $STUDIP/.gitlab/scripts/install_db.sh

if [ ! -z $AUTO_MIGRATE ]; then
    echo "Migrate Instance"
    # If migrate fails start instance anyway
    php "$STUDIP/cli/studip migrate" || true
    echo "Migration finished"
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"

