#!/bin/sh

# make sure that installed packages take precedence over busybox. 
# see https://dev.openwrt.org/ticket/18523
export PATH="/usr/bin:/usr/sbin:/bin:/sbin"

# Must match /etc/config/fstab
export rootUUID=05d615b3-bef8-460c-9a23-52db8d09e000
export dataUUID=05d615b3-bef8-460c-9a23-52db8d09e001
export swapUUID=05d615b3-bef8-460c-9a23-52db8d09e002

log() {
  /usr/bin/logger -t autoprov -s $*
}

getPendriveSize() {
  if (grep -q sda /proc/partitions) then
    cat /sys/block/sda/size
  else
    echo 0
  fi
}

hasBigEnoughPendrive() {
  local size=$(getPendriveSize)
  if [ $size -ge 600000 ]; then
    log "Found a pendrive of size: $(($size / 2 / 1024)) MB"
    return 0
  else
    return 1
  fi
}

makeParitions() {
  # Automount causes misery for us...
  /etc/init.d/mountd stop
  umount /dev/sda3 /dev/sda2 /dev/sda1
  swapoff /dev/sda1

  # erase partition table
  dd if=/dev/zero of=/dev/sda bs=1M count=1

  # sda1 is 'swap'
  # sda2 is 'root'
  # sda3 is 'data'
  fdisk /dev/sda <<EOF
o
n
p
1

+256M
n
p
2

+512M
n
p
3


t
1
82
w
q
EOF
  log "Finished partitioning /dev/sda using fdisk"

  sleep 2
  until [ -e /dev/sda1 ] ; do
      echo "Waiting for partitions to show up in /dev"
      sleep 1
  done

  mkswap -L swap -U $swapUUID /dev/sda1
  mkfs.ext4 -L root -U $rootUUID /dev/sda2
  mkfs.ext4 -L data -U $dataUUID /dev/sda3
  sleep 1

  mkdir -p /overlaynew
  mount -U $rootUUID /overlaynew
  mkdir -p /overlaynew/upper /overlaynew/work /overlaynew/upper/mnt/data
  tar -c -C /overlay . | tar -x -C /overlaynew

  mkdir -p /mnt/data /overlaynew/upper/mnt/data
  mount -U $dataUUID /mnt/data

  # write a new rc.local on the overlay that will shadow the one in rom 
  mkdir -p /overlaynew/upper/etc/
  cat > /overlaynew/upper/etc/rc.local <<EOF
# Provisioning Done.
EOF

  mkdir -p \
    /mnt/data/Shared \
    /mnt/data/Shared/incoming \
    /mnt/data/minidlna/db \
    /mnt/data/tmp \
    /overlaynew/upper/www

  touch /mnt/data/Shared/.noupload /overlaynew/upper/www/.noupload
  ln -s /mnt/data/Shared /overlaynew/upper/www/Shared

  cat > /mnt/data/Shared/incoming/readme.txt <<EOF
Feel free to upload any assorted stuff to share in this directory.

Be nice, or your stuff will get deleted. =)
EOF

  chown -R www:www /mnt/data/tmp /mnt/data/Shared
  chown -R minidlna:www /mnt/data/minidlna/

  mkdir -p \
    /overlaynew/upper/var/log/archive \
    /overlaynew/upper/var/lib \
    /overlaynew/upper/var/spool/cron/crontabs

  cat > /overlaynew/upper/var/spool/cron/crontabs/root <<EOF
0 0 * * * /usr/sbin/logrotate /etc/logrotate.conf
EOF

# Remove bad .extroot-uuid maybe?
  rm -rf /overlaynew/etc /overlaynew/.fs_state
  touch /mnt/data/.provdone
}

#############################################

if [ -f /mnt/data/.provdone ] ; then
  log "Provisioning already done."
else 
  log "Provisioning required."

  if [ -f /lib/ar71xx.sh ]; then
     . /lib/ar71xx.sh
  fi

  until hasBigEnoughPendrive ; do
    echo "Waiting for a pendrive to be inserted"
    sleep 3
  done

  if [ ! -e /dev/sda3 ] ; then 
    log "No sda3.  Partitioning..."
    makeParitions
    sync
  
    log "Rebooting..."
  
    umount /dev/sda3
    umount /dev/sda2
    rmdir /overlaynew
    sync
  
    reboot
  else
    log "sda3 exists but no provisioning file.  Insert a better stick?"
  fi
fi
