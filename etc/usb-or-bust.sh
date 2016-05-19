#!/bin/bash

echo "Checking for USB / sda existance..."
if [ ! -b /dev/sda ] ; then
        echo "No sda found.  We should be rebooting now..."
        if [ "$1" == "--reboot" ] ; then
                reboot
        else 
                sleep 5 
                $0 --reboot
        fi
else 
        echo "Looks like we've got USB."
fi
