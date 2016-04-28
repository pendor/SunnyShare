#!/bin/bash

set -e

if [ "$1" == "-r" ] ; then
  RESTART=1
  shift
else
  RESTART=0
fi

BOX="$1"
if [ "$BOX" == "" ] ; then
  echo "Usage: $0 [box name] <- Assuming 'sunny'"
  BOX="sunny"
fi

cd copy-to/files
scp -r . $BOX:/

if [ "$RESTART" == "1" ] ; then
  echo " "
  echo " "
  echo "Restarting lighttpd..."
  echo " "
  ssh $BOX 'killall -9 lighttpd ; sleep 2 ; /etc/init.d/lighttpd start'
fi

echo " "
echo " "
echo "Deployed."
echo " "
