#!/bin/bash
set -e

if [ $(mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE -e "show tables;" --batch | wc -l) -eq 0 ]; then

    # Setup mysql database
    echo "INSTALL DB"
    mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip.sql
    echo "INSTALL DEFAULT DATA"
    mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_default_data.sql
    mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_resources_default_data.sql

    echo "INSTALL ROOTUSER"
    mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_root_user.sql

    # Check if demodata is required
    if [ ! -z $DEMO_DATA ]; then
        echo "INSTALL DEMODATA"
        mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_demo_data.sql
        echo "INSTALL MVV_DEMODATA"
        mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_mvv_demo_data.sql
        echo "INSTALL RESOURCES-DEMODATA"
        mysql -u $MYSQL_USER -h $MYSQL_HOST -p$MYSQL_PASSWORD $MYSQL_DATABASE < ./db/studip_resources_demo_data.sql
    fi

    echo "INSTALLATION FINISHED"
else
    echo "Found some SQL table. Skipping installation"
fi
