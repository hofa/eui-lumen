#!/bin/bash

set -e

cron

/usr/bin/php /var/www/GatewayWorker/GatewayWorker/start.php start -d

exec "$@"