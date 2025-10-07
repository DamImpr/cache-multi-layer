#!/bin/sh


case "$1" in
    'test-sw')
        exec ./vendor/bin/phpunit ./tests/
        ;;
    'update-vendor')
        exec composer update --no-interaction --no-scripts --no-autoloader --prefer-dist
        ;;
    'php-cs-fixer')
        exec php vendor/bin/php-cs-fixer fix --allow-risky=yes --diff
        ;;
    'rector')
        exec php vendor/bin/rector process
        ;;
    'phpstan')
        exec php vendor/bin/phpstan analyse -c phpstan.neon
        ;;
    'sh')
        exec sh
        ;;
    *)
        echo "comands allowed"
        echo " - test-sw"
        echo " - update-vendor"
        echo " - php-cs-fix"
        echo " - rector"
        echo " - phpstan"
        echo " - sh"
        exit 1
        ;;
esac


