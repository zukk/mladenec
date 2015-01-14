#!/bin/sh

# полный путь до скрипта
ABSOLUTE_FILENAME=`readlink -e "$0"`

# каталог в котором лежит скрипт
DIRECTORY=`dirname "$ABSOLUTE_FILENAME"`

DAEMON_FILE="$DIRECTORY/../../daemon/daemon.php"
LOG_FILE="$DIRECTORY/../../logs/$(date +%Y)/$(date +%m)/$(date +%d)_daemon.log"

php $DAEMON_FILE >> $LOG_FILE

## echo "Log in:"
## echo $LOG_FILE