@echo off
set experiment=%1
set configuration=%2

php generate-queue.php -e %experiment% -c %configuration%
php execute.php -c %configuration%
php generate-standings.php -e %experiment% -c %configuration%
php generate-plots.php -e %experiment% -c %configuration%
php print.php -e %experiment% -c %configuration%
