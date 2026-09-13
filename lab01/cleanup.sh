#!/bin/sh

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
    set -- "$@" "tmp"
fi

shift

COUNTER="/tmp/cleanup_$$.count"
echo 0 > "$COUNTER"

for ext in "$@"; do
    ext=$(printf '%s' "$ext" | sed 's/^\.//')
    
    find "$DIR" -type f -name "*.$ext" 2>/dev/null | while IFS= read -r file; do
        if rm -f "$file" 2>/dev/null; then
            echo "Deleted: $file"
            count=$(cat "$COUNTER")
            echo $((count + 1)) > "$COUNTER"
        else
            echo "Warning: failed to delete '$file'"
        fi
    done
done

TOTAL=$(cat "$COUNTER")
rm -f "$COUNTER"

echo "Total files deleted: $TOTAL"