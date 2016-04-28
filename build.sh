#!/bin/bash

PROFILE=TLWR842
BUILD_DIR='~/OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64'
# BUILD_DIR='~/OpenWrt-ImageBuilder-15.05.1-ar71xx-generic.Linux-x86_64'
FW_DIR="${BUILD_DIR}/bin"
OUT='openwrt-*-wr842n-v2-squashfs-factory.bin'
LOCAL_FW=/Users/pendor/OpenWRT/firmware

INSTALL="false"
if [ "$1" == "--install" ] ; then
  INSTALL="true"
  shift
fi

PKG_FILE="$1"

if [ -z $PKG_FILE ] ; then
  PKG_FILE=pb.pkgs
  echo "Using default package file: $PKG_FILE.  Pass another on the command line to override."
fi

rm -rf $LOCAL_FW
mkdir -p $LOCAL_FW

vagrant up

PKGS=`cat $PKG_FILE | grep -v '#' | tr '\r\n' ' '`

find copy-to -name .DS_Store -delete
vagrant ssh -c "\
  cd ${BUILD_DIR} ; \
  make image PROFILE=${PROFILE} PACKAGES=\"${PKGS}\" FILES=/openwrt_files/files && \
  cp -r ${BUILD_DIR}/bin/* /openwrt_firmware \
  "

if [ -f $LOCAL_FW/ar71xx/$OUT ] ; then
  echo " "
  echo " "
  echo "Found $LOCAL_FW/ar71xx/$OUT.  Looks like build is good."
  echo " "
  echo " "
  if [ "$INSTALL" == "true" ] ; then
    echo "Sending firmware to the condemned..."
    scp $LOCAL_FW/ar71xx/$OUT root@192.168.1.1:/tmp/owrt-pbox.bin

    echo "Bricking router now..."
    ssh root@192.168.1.1 'sysupgrade -n -v /tmp/owrt-pbox.bin'

    echo "You now have a brick.  Perhaps it's rebooting?"
  fi
else
  echo "No firmware found.  Build failed?"
fi
