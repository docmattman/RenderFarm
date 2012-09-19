#!/bin/sh
while true
do
 /usr/bin/php /render/app/worker_app/cron/check_in.php > /dev/null
 sleep 10
done
