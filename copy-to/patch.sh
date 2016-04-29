#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 <patchdir>"
  exit 1
fi

PATCHDIR=`dirname $0`/patches/$1

if [ ! -d $PATCHDIR ] ; then
  echo "Error: Patchder $PATCHDIR not found."
  exit 1
fi

echo "Applying patches from $PATCHDIR..."
for f in $PATCHDIR/*.patch ; do
  echo -n "  -- $f : "
  if patch -p1 -N --dry-run -i $f ; then 
    echo -n "Applying..."
    if patch -p1 -N -i $f ; then
      echo "  [OK]"
    else
      echo "  [FAILED]"
    fi
  else
    echo "  Not required"
  fi
done
