#!/bin/bash

[ $# -lt 1 ] && { echo "Usage: ${0} <dir> [ext...]"; exit 1; }
[ -d "${1}" ] || { echo "Error: '${1}' not found"; exit 1; }

dir=${1}; shift
[ $# -eq 0 ] && set -- tmp

count=0
for ext; do
    n=$(find "${dir}" -type f -name "*.${ext#.}" -print -delete | wc -l)
    ((count+=n))
done
echo "Deleted: ${count}"