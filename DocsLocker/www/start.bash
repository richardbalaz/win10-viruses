#!/bin/bash
cd /var/www/html/DocsLocker
rm events.log
touch events.log
chmod -R 777 events.log
clear
cat logo.txt
tail -f events.log
