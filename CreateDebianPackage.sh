#!/bin/bash
if test -z $1
then
  echo "Please specify a version number (i. e. 1.0.0)."
  exit 0;
fi
if [[ $2 -lt 1 ]]
then
	echo "Please provide a revision number."
	exit 0;
fi
if test -z $3
then
  echo "Please specify a distribution (i. e. wheezy)."
  exit 0;
fi
version=$1

rm -Rf homegear-mediola-ewickler*
mkdir homegear-mediola-ewickler-$version
cp -R HM-XMLRPC-Client *.php *.xml *.json debian homegear-mediola-ewickler-$version
date=`LANG=en_US.UTF-8 date +"%a, %d %b %Y %T %z"`
echo "homegear-mediola-ewickler ($version-$2) $3; urgency=low


 -- Michael Landherr  $date" > homegear-mediola-ewickler-$version/debian/changelog
tar -zcpf homegear-mediola-ewickler_$version.orig.tar.gz homegear-mediola-ewickler-$version
cd homegear-mediola-ewickler-$version
debuild -us -uc
