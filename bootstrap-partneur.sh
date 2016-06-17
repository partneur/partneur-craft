#!/bin/bash
rm -rf /var/www/html
ln -sf /vagrant /var/www/html
mysql -uroot -pvagrant < /vagrant/create-craft-db.sql

## Borrowed from craftyvagrant db script

## Check whether there's a database backup:
if [ -d "/vagrant/craft/storage/backups" ]; then
	db_init=`ls /vagrant/craft/storage/backups | tail -1`;
else
	db_init='';
fi

## If there is, restore it:
if test ! -s $db_init
then
	echo
	echo "Populating Craft database from latest backup ($db_init)";
	mysql -uroot -pvagrant craft < /vagrant/craft/storage/backups/$db_init;
	echo
else
	echo
	echo 'No Craft database backups found. Please install Craft manually.';
	echo
fi
