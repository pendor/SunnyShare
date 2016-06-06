#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 <ssh_host>"
  exit 1
fi

if [ "$2" != "" ] ; then
  tgt="$2"
else
  tgt="all"
fi

# mkdir -p dl
# if [ ! -f dl/compiler.jar ] ; then
#   if [ ! -f dl/compiler-latest.zip ] ; then
#     wget -P dl https://dl.google.com/closure-compiler/compiler-latest.zip
#   fi
#
#   unzip -d dl dl/compiler-latest.zip
# fi

if [ $tgt == "all" ] || [ $tgt == "www" ] ; then
  rm -f www/combo.js
  # cat www/js/*.js > www/combo-max.js
  # java -jar dl/compiler.jar --third_party --compilation_level=SIMPLE --js=www/combo-max.js --js_output_file=www/combo.js
  cat www/js/*.js > www/combo.js
  scp -r www/* www-data@${1}:/var/www/html/
fi

if [ $tgt == "all" ] || [ $tgt == "etc" ] ; then
  scp -r etc/* root@${1}:/etc/
fi

if [ $tgt == "all" ] || [ $tgt == "boot" ] ; then
  scp -r boot/* root@${1}:/boot/
	echo "Compiling boot files on device..."
	ssh root@${1} 'cd /boot ; ./compile.sh'
fi

