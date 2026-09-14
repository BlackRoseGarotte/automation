#!/bin/bash

if [ $# -lt 1 ]; then
    echo "Error: Directory path is required."
    echo "Usage: $0 <directory> [extension1] [extension2] ..."
    exit 1
fi

DIR="$1"

if [ ! -d "$DIR" ]; then
    echo "Error: Directory '$DIR' does not exist."
    exit 1
fi

if [ $# -eq 1 ]; then
    EXTENSIONS=("tmp")
else
    EXTENSIONS=("${@:2}")
fi

DELETED_COUNT=0

for ext in "${EXTENSIONS[@]}"; do
    ext="${ext#.}"
    while IFS= read -r -d '' file; do
        if rm -f "$file"; then
            ((DELETED_COUNT++))
            echo "Deleted: $file"
        else
            echo "Warning: failed to delete '$file'"
        fi
    done < <(find "$DIR" -type f -name "*.$ext" -print0)
done

echo "Total files deleted: $DELETED_COUNT"
