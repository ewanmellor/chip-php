#!/bin/sh

#
# CHIP: Code Highlighting in PHP.
#
# Copyright (c) 2004-2005 Ewan Mellor <chip@ewanmellor.org.uk>.  All rights
# reserved.
#


if [ "$EUID" != "0" ]
then
  exec fakeroot "$0"
fi

tempdir=`mktemp -d`

function cleanup()
{
  rm -R "$tempdir"
}

trap cleanup EXIT

VERSION=$(cat README | grep Version | sed -e 's/Version //g')
DIST="$tempdir/chip"
WD=$(pwd)
TAR="$WD/chip-$VERSION.tar.bz2"

if [ -e "$TAR" ]
then
  rm "$TAR"
fi

mkdir -p "$DIST/debian"
mkdir -p "$DIST/tests"

cp chip.php     "$DIST"
cp chip*.css    "$DIST"
cp chip.js      "$DIST"
cp rot13.js     "$DIST"
cp chip-*.php   "$DIST"
cp README       "$DIST"
cp make-dist.sh "$DIST"

cp debian/50chip.conf   "$DIST/debian"
cp debian/control       "$DIST/debian"
cp debian/instrm-helper "$DIST/debian"
cp debian/postinst      "$DIST/debian"
cp debian/README.Debian "$DIST/debian"
cp debian/conffiles     "$DIST/debian"
cp debian/make-deb.sh   "$DIST/debian"
cp debian/prerm         "$DIST/debian"

cp tests/run-tests.sh   "$DIST/tests"
cp tests/*.expected     "$DIST/tests"
cp tests/*.html         "$DIST/tests"
cp tests/*.jl           "$DIST/tests"
cp tests/*.js           "$DIST/tests"
cp tests/*.php          "$DIST/tests"
cp tests/*.py           "$DIST/tests"

find "$DIST" -type d -exec chmod ug=rwx,o=rx \{\} \;
find "$DIST" -type f -exec chmod ug=rw,o=r   \{\} \;
find "$DIST" -name '*.sh' -exec chmod a+x    \{\} \;

(cd "$tempdir" && tar cjf "$TAR" "chip")

md5sum "$TAR" >"$TAR.md5"
