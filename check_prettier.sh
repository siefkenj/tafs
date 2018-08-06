#!/bin/bash

ALL_FORMATTED=1
for vue_file in `find src -name "*vue"`
do
	MD5=`cat "$vue_file" | md5sum`
	FORMATTED_MD5=`prettier --tab-width 4 --no-config "$vue_file" | md5sum`

	if [ "$MD5" = "$FORMATTED_MD5" ]
	then
		echo "       $vue_file formatted with prettier"
	else
		echo "ERROR: $vue_file NOT formatted with prettier '<' is your file."
		# print out a diff of the output so we can see what's going on.
		prettier --tab-width 4 --no-config "$vue_file" | diff "$vue_file" -
		ALL_FORMATTED=0
	fi
done

if [ "$ALL_FORMATTED" -eq "0" ]
then
	exit 1
fi
