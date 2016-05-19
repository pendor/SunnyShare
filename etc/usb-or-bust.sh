#!/bin/bash

if [ ! -b /dev/sda ] ; then
	if [ ! -f /.nousbok ] ; then
		reboot
	fi
fi
