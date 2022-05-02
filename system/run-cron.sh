#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")"
# php streams/cron.php > /dev/null 2>&1 &
php analytics/record_all.php > /dev/null 2>&1 &
php devices/tick_handlers.php > /dev/null 2>&1 &