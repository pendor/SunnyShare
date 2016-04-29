#!/bin/bash


# SSH alias or user@ip for box:
BOX=sunny

# Router type
#REL_DIR=snapshots/trunk
# PROFILE=TLWR842
# FW_PROFILE=tl-wr842n-v2
# ARCH=ar71xx
# SUB_ARCH=generic
# SUFFIX=squashfs-factory.bin 
# RELEASE=trunk
# BUILD_ABI=gcc-4.8-linaro_uClibc-0.9.33.2

REL_DIR=chaos_calmer/15.05.1
PROFILE=RaspberryPi2
ARCH=brcm2708
FW_PROFILE=
SUB_ARCH=bcm2709
SUFFIX=sdcard-vfat-ext4.img
RELEASE=15.05.1
BUILD_ABI=gcc-4.8-linaro_uClibc-0.9.33.2_eabi

HOST_ARCH=Linux-x86_64


if [ "$RELEASE" == "trunk" ] ; then
  REL_TAG=""
else
  REL_TAG="${RELEASE}-"
fi

# OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64 
# OpenWrt-ImageBuilder-15.05.1-brcm2708-bcm2709.Linux-x86_64
# Dir within the VM for ImageBuilder.
BUILD_DIR="OpenWrt-ImageBuilder-${REL_TAG}${ARCH}-${SUB_ARCH}.${HOST_ARCH}"
SDK_DIR="OpenWrt-SDK-${REL_TAG}${ARCH}-${SUB_ARCH}_${BUILD_ABI}.${HOST_ARCH}"

# ar71xx/openwrt-ar71xx-generic-tl-wr842n-v2-squashfs-factory.bin 
# brcm2708/openwrt-15.05.1-brcm2708-bcm2709-sdcard-vfat-ext4.img
# Name of the firmware we check for output.
OUT="${BUILD_DIR}/bin/${ARCH}/openwrt-${REL_TAG}${ARCH}-${SUB_ARCH}${FW_PROFILE}-${SUFFIX}"

IB_URL="https://downloads.openwrt.org/${REL_DIR}/${ARCH}/${SUB_ARCH}/${BUILD_DIR}.tar.bz2"
SDK_URL="https://downloads.openwrt.org/${REL_DIR}/${ARCH}/${SUB_ARCH}/${SDK_DIR}.tar.bz2"

# Where we'll drop the finished firmware on the host system
LOCAL_FW=./firmware

PKGS=`cat $PKG_FILE | grep -v '#' | tr '\r\n' ' '`
PKGS_ADD=`cat $PKG_FILE | grep -v '#' | egrep -v '^-.*' | tr '\r\n' ' '`

function space() {
  echo ""
  echo ""
  if [ "$1" != "" ] ; then
    echo "$1"
    echo ""
    echo ""
  fi
}

function checkDir() {
  local VM_DIR="$1"
  local TARBALL="$2"
  local URL="$3"
  
  echo "Checking for ${VM_DIR} in VM..."
  if ! vagrant ssh -c "[ -d ${VM_DIR} ]" ; then
    # No build dir, so download it or untar it.
    echo "  -- Missing.  Checking for local download."
    if [ ! -f dl/${TARBALL} ] ; then
      # No tarball.  Download it.
      echo "  -- Need to download ${URL}..."
      wget -P dl "${URL}" || ( echo "Download failed" && exit 1 )
    fi
    # Uncompress
    echo "  -- Extracting tarball ${TARBALL} in VM..."
    vagrant ssh -c "tar -xf /openwrt_dl/${TARBALL}" || ( echo "Extract failed" && exit 1 )
  fi
}