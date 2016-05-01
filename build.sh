#!/bin/bash

set -e

PKG_FILE="$1"

if [ -z $PKG_FILE ] ; then
  PKG_FILE=pb.pkgs
  echo "Using default package file: $PKG_FILE.  Pass another on the command line to override."
fi

source ./settings.sh

PKG_FILE="$1"

if [ -z $PKG_FILE ] ; then
  PKG_FILE=pb.pkgs
  echo "Using default package file: $PKG_FILE.  Pass another on the command line to override."
fi

rm -rf $LOCAL_FW
mkdir -p $LOCAL_FW

space "Checking Image Builder."

vagrant up

# VM is up & has basic tools.  Let's make sure we have Image Builder
checkDir "${BUILD_DIR}" "${BUILD_DIR}.tar.bz2" "${IB_URL}" || exit 1

# Should have ImageBuilder ready.  Time to build...
space "Image Builder looks good.  Let's try to build..."

PKGS=`cat $PKG_FILE | grep -v '#' | tr '\r\n' ' '`

find copy-to -name .DS_Store -delete
vagrant ssh -c "\
  cd ${BUILD_DIR} && \
  rm -rf files && \
  cp -r /openwrt_files/* . && \
  /openwrt_files/patch.sh imagebuilder && \
  make image PROFILE=${PROFILE} PACKAGES=\"${PKGS}\" FILES=files && \
  cp -r bin /openwrt_firmware \
  "


if [ -f $LOCAL_FW/$OUT ] ; then
  space "Found $LOCAL_FW/$OUT.  Looks like build is good."
else
  space "No firmware found.  Build failed?"
fi
