#!/bin/bash

# Make sure that the filesystem is mounted correctly with mfsymlinks (CIFS only?)
content=$(cat /mnt/huhimagestorage/huhspecimenimages/symlink_testfile.txt)
if [ "Symlinks working!" != "$content" ];then
    echo "ERROR: huhimagestorage/ is not mounted with mfsymlinks"
    exit 1
fi

# run the sync - excluding CR2 and DNG for now
aws s3 sync --quiet --size-only --exclude "*.dng" --exclude "*.CR2" /mnt/huhimagestorage/huhspecimenimages/ s3://huhspecimenimages/