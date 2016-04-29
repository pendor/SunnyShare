#!/bin/bash

source ./settings.sh

space

if [ -f $LOCAL_FW/$OUT ] ; then
  echo "Sending firmware $OUT to the condemned..."
  scp $LOCAL_FW/$OUT $BOX:/tmp/owrt-pbox.bin

  echo "Bricking router now..."
  ssh $BOX 'sysupgrade -n -v /tmp/owrt-pbox.bin'

  space "You now have a brick.  Perhaps it's rebooting?"
else
  space "No firmware found.  Build failed?"
fi
