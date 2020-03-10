experiment=$1
configuration=$2

php generate-queue.php -e $experiment -c $configuration
php execute.php -c $configuration
php extend-results.php -e $experiment -c $configuration
php generate-standings.php -e $experiment -c $configuration --force
php generate-plots.php -e $experiment -c $configuration --force
php print.php -e $experiment -c $configuration
php notify.php -e $experiment -c $configuration
