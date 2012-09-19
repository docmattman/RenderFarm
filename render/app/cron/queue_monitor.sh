#!/bin/sh
while true
do
 /usr/bin/php /render/app/cron/queue.php > /dev/null
 sleep 2
done
