#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 [user@]<ssh_host>"
  exit 1
fi

PKGS="lighttpd php-cli php-cgi ifplugd"

ssh "$1" 'apt-get update && apt-get install $PKGS'
