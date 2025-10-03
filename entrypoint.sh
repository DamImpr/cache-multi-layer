#!/bin/sh


if [ "$1" = 'test-sw' ]; then
    exec ./vendor/bin/phpunit ./tests/ 
elif [ "$1" = 'update-vendor' ]; then
    exec composer update --no-interaction --no-scripts --no-autoloader --prefer-dist
elif [ "$1" = 'php-cs-fix' ]; then
    exec php vendor/bin/php-cs-fixer fix --allow-risky=yes --diff
elif [ "$1" = 'rector' ]; then
    exec php vendor/bin/rector process
elif [ "$1" = 'phpstan' ]; then
    exec php vendor/bin/phpstan analyse -c phpstan.neon
elif [ "$1" = 'sh' ]; then
    exec sh
fi


echo "comands allowed"
echo " - test-sw"
echo " - update-vendor"
echo " - php-cs-fix"
echo " - rector"
echo " - phpstan"
echo " - sh"