#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 [user@]<ssh_host>"
  exit 1
fi


rm -f copy-to/files/www/combo.js
cat copy-to/files/www/*.js > copy-to/files/www/combo.js
scp -r copy-to/files/www/* ${1}:/var/www/html/
