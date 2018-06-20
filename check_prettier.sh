#!/bin/bash

ALL_FORMATTED=1

# check vue files
for vue_file in `find src -name "*vue"`
do
	MD5=`cat "$vue_file" | md5sum`
	FORMATTED_MD5=`prettier --tab-width 4 --no-config "$vue_file" | md5sum`

	if [ "$MD5" = "$FORMATTED_MD5" ]
	then
		echo "       $vue_file formatted with prettier"
	else
		echo "ERROR: $vue_file NOT formatted with prettier"
		ALL_FORMATTED=0
	fi
done

# check php files
for php_file in *php
do
	MD5=`cat "$php_file" | md5sum`
	FORMATTED_MD5=`prettier --tab-width 4 --parser php --no-config "$php_file" | md5sum`

	if [ "$MD5" = "$FORMATTED_MD5" ]
	then
		echo "       $php_file formatted with prettier"
	else
		echo "ERROR: $php_file NOT formatted with prettier"
		ALL_FORMATTED=0
	fi
done

if [ "$ALL_FORMATTED" -eq "0" ]
then
	exit 1
fi
