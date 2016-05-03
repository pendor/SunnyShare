#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 <ssh_host>"
  exit 1
fi


rm -f www/combo.js
cat www/*.js > www/combo.js
scp -r www/* www-data@${1}:/var/www/html/
scp -r etc/* root@${1}:/etc/
