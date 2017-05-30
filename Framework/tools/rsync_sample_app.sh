#!/bin/bash

export APP_ROOT=$1
export SRC_PASSWD=GMQJatXtbZfndxrvoiAZDGSCpJ8sHLmE
export SRC_PORT=2222
export SRC_HOST=main.jp-yushun@ssh461.lolipop.jp
export SRC_ROOT=/home/users/1/main.jp-yushun/web/app00.kyukoh.net/club.kyukoh.net/

export dry_run="--dry-run"
if [ "$2" = "run" ]; then
    export dry_run=""
fi

comm="sshpass -p $SRC_PASSWD rsync $dry_run -rlcgzopDv --files-from=sync_files.txt --exclude='._*' -e 'ssh -p $SRC_PORT' $SRC_HOST:$SRC_ROOT $APP_ROOT"
echo $comm
eval ${comm}
