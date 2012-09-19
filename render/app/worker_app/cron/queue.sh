#!/bin/sh
while true
do
 /usr/bin/php /render/app/worker_app/cron/queue.php
 sleep 2
done
