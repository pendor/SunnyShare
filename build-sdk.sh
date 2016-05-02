#!/bin/bash

set -e

PKG_FILE="$1"

if [ -z $PKG_FILE ] ; then
  PKG_FILE=pb-pi.pkgs
  echo "Using default package file: $PKG_FILE.  Pass another on the command line to override."
fi


source ./settings.sh

rm -rf $LOCAL_FW
mkdir -p $LOCAL_FW

space "Checking SDK state."

vagrant up

# VM is up & has basic tools.  Let's make sure we have the SDK"

checkDir "${SDK_DIR}" "${SDK_DIR}.tar.bz2" "${SDK_URL}" || exit 1

# Should have ImageBuilder ready.  Time to build...
space "SDK looks good.  Let's try to build..."


find copy-to -name .DS_Store -delete
vagrant ssh -c "\
  cd ${SDK_DIR} && \
  cp /openwrt_files/sdk-dot-config .config && \
  ./scripts/feeds update -a && \
  ./scripts/feeds install ${PKGS_ADD} && \
  /openwrt_files/patch.sh sdk && \
  make ${MAKE_OPTS} world
"
  # rm -rf files && \
  # cp -r /openwrt_files/* . && \
  # ( patch -p0 -N --dry-run -i Makefile.patch && patch -p0 -N -i  Makefile.patch ) ;  \
  # make image PROFILE=${PROFILE} PACKAGES=\"${PKGS}\" FILES=files && \
  # cp -r bin /openwrt_firmware \
  # "

if [ -f $LOCAL_FW/$OUT ] ; then
  space "Found $LOCAL_FW/$OUT.  Looks like build is good."
else
  space "No firmware found.  Build failed?"
fi
