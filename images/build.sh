#!/bin/bash

convert images/sun_large.jpg -threshold 90% -resize 64x64 etc/oled/sun.png

convert sun_large.jpg -threshold 90% -resize 64x64 -gravity center -extent 128x64 sun.xbm 