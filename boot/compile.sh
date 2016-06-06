#!/bin/bash

cd bin
for f in *.fex ; do
	T=`basename $f .fex`.bin
	echo "$f -> $T"
	fex2bin $f $T
done

cd ../dtb
for f in *.dts ; do
	T=`basename $f .dts`.dtb
	echo "$f -> $T"
	dtc -O dtb -o $T -b 0 $f
done
