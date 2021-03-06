#!/bin/bash
# Mostly proof of concept for a brute force i2c write to the screen

set -e

CHARGEPUMP="0x8D"
COLUMNADDR="0x21"
COMSCANDEC="0xC8"
COMSCANINC="0xC0"
DISPLAYALLON="0xA5"
DISPLAYALLON_RESUME="0xA4"
DISPLAYOFF="0xAE"
DISPLAYON="0xAF"
EXTERNALVCC="0x01"
INVERTDISPLAY="0xA7"
MEMORYMODE="0x20"
NORMALDISPLAY="0xA6"
PAGEADDR="0x22"
SEGREMAP="0xA0"
SETCOMPINS="0xDA"
SETCONTRAST="0x81"
SETDISPLAYCLOCKDIV="0xD5"
SETDISPLAYOFFSET="0xD3"
SETHIGHCOLUMN="0x10"
SETLOWCOLUMN="0x00"
SETMULTIPLEX="0xA8"
SETPRECHARGE="0xD9"
SETSEGMENTREMAP="0xA1"
SETSTARTLINE="0x40"
SETVCOMDETECT="0xDB"
SWITCHCAPVCC="0x02"

echo "FIXME: This isn't outputting 0x00 bytes properly..."
exit 1

rm -f ../boot/i2c-cmd.bin
touch ../boot/i2c-cmd.bin
# init the screen:
for b in \
  $DISPLAYOFF \
  $SETDISPLAYCLOCKDIV 0x80 \
  $SETMULTIPLEX 0x3F \
  $SETDISPLAYOFFSET 0x00 \
  $SETSTARTLINE \
  $CHARGEPUMP 0x14 \
  $MEMORYMODE 0x00 \
  $SEGREMAP \
  $COMSCANDEC \
  $SETCOMPINS 0x12 \
  $SETCONTRAST 0xCF \
  $SETPRECHARGE 0xF1 \
  $SETVCOMDETECT 0x40 \
  $DISPLAYALLON_RESUME \
  $NORMALDISPLAY \
  $DISPLAYON \
  $COLUMNADDR 0x00 0x7f \
  $PAGEADDR 0x00 0x3f ; do
  printf "0: %x" $b | xxd -r -g0 >> ../boot/i2c-cmd.bin
done
