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

if [ ! -f /mnt/data/chat.json ] ; then
	echo '[{"t":1464738789,"n":"Sunny+Share","m":"If you have any suggestions for improving this service, please leave a note here.","c":"#B68CC2","i":"ae9467c203025852b98353a03da4abe25460de70"}]' \
		> /mnt/data/chat.json
	chown www-data /mnt/data/chat.json
	rm -f /var/www/html/chat.json
fi

if [ ! -L /var/www/html/chat.json ] ; then
	rm -f /var/www/html/chat.json
	ln -s /mnt/data/chat.json /var/www/html/chat.json
fi

cat > /mnt/data/Shared/readme.txt <<EOF
This file sharing area allows you to upload things others might find interesting.  
You can upload images, videos, music, books, writings, pretty much anything.

You can download any files you like from this web page.  If you're trying to play 
video or music on your mobile device, you'll have better results using a UPnP 
compatible media player to stream content rather than downloading it.

On iPhone, try OPlayer: https://itunes.apple.com/us/app/video-player-oplayer-classic/id344784375?mt=8

On other platforms, search your app store for "upnp"
EOF

find /mnt/data -name ._\* -delete
find /mnt/data -name .DS_Store -delete
