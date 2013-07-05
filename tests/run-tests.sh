#!/bin/sh

testsdir=$(dirname "$0")
CHIP=$(readlink -f "$testsdir/../chip.php")

pathfind()
{
  OLDIFS="$IFS"
  IFS=:
  for p in $PATH
  do
    if [ -x "$p/$*" ]
    then
      IFS="$OLDIFS"
      return 0
    fi
  done
  IFS="$OLDIFS"
  return 1
}

if pathfind "php4"
then
  PHP="php4"
elif pathfind "php"
then
  PHP="php"
else
  echo "Cannot find PHP CLI executable."
  exit 1
fi

tmpdir=`mktemp -d`

function cleanup
{
  rm -Rf "$tmpdir"
}

trap cleanup EXIT

cd "$testsdir"

for file in *.expected
do
  in="${file/.expected/}"
  exp="$file"
  out="$tmpdir/${file/.expected/.out}"

  "$PHP" "$CHIP" "$in" |
    sed -e 's#, version [0-9]\+\.[0-9]\+\.[0-9]\+#, version X.Y.Z#g' >"$out"

  diff "$exp" "$out"
done
