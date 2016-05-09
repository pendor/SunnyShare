#!/bin/bash
# Try to stay off the internal wifi & find something better

if ( iwgetid wlan1 | grep -q 'Sunny+Share' ) ; then 
  ifdown wlan1
  sleep 10
  ifup wlan1
fi


# FIXME: Need to parse this output to find open wifi and not bother if nothing
# open or in our preferred list?
# iwlist wlan1 scan
# wlan1     Scan completed :
#           Cell 01 - Address: 00:F7:6F:CF:39:66
#                     ESSID:"cxxxx{}:::::::::::::>"
#                     Protocol:IEEE 802.11bgn
#                     Mode:Master
#                     Frequency:2.437 GHz (Channel 6)
#                     Encryption key:on
#                     Bit Rates:144 Mb/s
#                     Extra:rsn_ie=30140100000fac040100000fac040100000fac020000
#                     IE: IEEE 802.11i/WPA2 Version 1
#                         Group Cipher : CCMP
#                         Pairwise Ciphers (1) : CCMP
#                         Authentication Suites (1) : PSK
#                     Quality=100/100  Signal level=100/100
#           Cell 02 - Address: 04:8D:38:D6:C2:40
#                     ESSID:"Sunny+Share Share Freely!"
#                     Protocol:IEEE 802.11bgn
#                     Mode:Master
#                     Frequency:2.442 GHz (Channel 7)
#                     Encryption key:off
#                     Bit Rates:130 Mb/s
#                     Quality=48/100  Signal level=91/100
