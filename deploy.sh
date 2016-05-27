#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 <ssh_host>"
  exit 1
fi


mkdir -p dl

# if [ ! -f dl/compiler.jar ] ; then
#   if [ ! -f dl/compiler-latest.zip ] ; then
#     wget -P dl https://dl.google.com/closure-compiler/compiler-latest.zip
#   fi
#
#   unzip -d dl dl/compiler-latest.zip
# fi

rm -f www/combo.js
# cat www/js/*.js > www/combo-max.js
# java -jar dl/compiler.jar --third_party --compilation_level=SIMPLE --js=www/combo-max.js --js_output_file=www/combo.js
cat www/js/*.js > www/combo.js
scp -r www/* www-data@${1}:/var/www/html/
scp -r etc/* root@${1}:/etc/
scp -r boot/* root@${1}:/boot/
