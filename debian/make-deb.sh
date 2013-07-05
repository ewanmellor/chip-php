#!/bin/sh

set -eu

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

S=$(dirname "$0")/..
C="$tempdir/usr/share/chip"
D="$tempdir/usr/share/doc/chip"
E="$tempdir/etc/chip"
DEB="$tempdir/DEBIAN"
A="$tempdir/etc/apacheconf/visible.d"

mkdir -p "$C"
mkdir -p "$D"
mkdir -p "$E"
mkdir -p "$DEB"
mkdir -p "$A"

version=$(cat README | grep Version | sed -e 's/Version //g')
bareversion=$(echo "$version" | sed -e 's#-.*##g')

cat "$S/debian/control" |
  sed -e "s#^Version: \(.*\)\$#Version: $bareversion#g" \
  >"$DEB/control"

chmod a=r "$DEB/control"

for file in conffiles postinst postrm preinst prerm shlibs
do
  if [ -f "$S/debian/$file" ]
  then
    cp "$S/debian/$file" "$DEB"
    chmod a=rx "$DEB/$file"
  fi
done

cp *.php "$C"
cp *.js  "$C"

cp debian/instrm-helper "$C"

cp chip.css    "$E"
cp chip-ie.css "$E"

cp README               "$D"
cp debian/README.Debian "$D"

cp debian/50chip.conf "$A"

find "$C" -type d -exec chmod a=rx        \{\} \;
find "$C" -type f -exec chmod a=r         \{\} \;
find "$E" -type d -exec chmod u=rwx,go=rx \{\} \;
find "$E" -type f -exec chmod u=rw,go=r   \{\} \;
find "$D" -type d -exec chmod a=rx        \{\} \;
find "$D" -type f -exec chmod a=r         \{\} \;
find "$A" -type d -exec chmod u=rwx,go=rx \{\} \;
find "$A" -type f -exec chmod u=rw,go=r   \{\} \;

DEB="chip_${version}_all.deb"

dpkg-deb --build "$tempdir" "$DEB"
md5sum "$DEB" >"$DEB.md5"
