#!/bin/bash

mkdir -p /mnt/data/tmp
chmod 777 /mnt/data/tmp

mkdir -p /mnt/data/minidlna/db /mnt/data/minidlna/log
chown -R minidlna /mnt/data/minidlna

mkdir -p /mnt/data/daapd/log /mnt/data/daapd/cache/
chown -R daapd /mnt/data/daapd

mkdir -p /mnt/data/roots /mnt/data/Shared
chown -R www-data /mnt/data/roots /mnt/data/Shared
touch /mnt/data/Shared/.noupload

cat > /mnt/data/Shared/readme.txt <<EOF
This file sharing area allows you to upload things others might find interesting.  
You can upload images, videos, music, books, writings, pretty much anything.

You can download any files you like from this web page.  If you're trying to play 
video or music on your mobile device, you'll have better results using a UPnP 
compatible media player to stream content rather than downloading it.

On iPhone, try OPlayer: https://itunes.apple.com/us/app/video-player-oplayer-classic/id344784375?mt=8

On other platforms, search your app store for "upnp"
EOF

