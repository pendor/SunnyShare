#!/bin/bash

cd bin
for f in *.bin ; do
	bin2fex $f `basename $f .bin`.fex
done

cd ../dtb
for f in *.dtb ; do
	dtc -I dtb -O dts $f > `basename $f .dtb`.dts
done
