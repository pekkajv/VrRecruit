<?php
// Here you can initialize variables that will for your tests
putenv('APPLICATION_ENV=test');
$cliIndex = implode(DIRECTORY_SEPARATOR, ['vreasy', 'application', 'cli', 'cliindex.php']);

require_once(\Codeception\Configuration::projectDir() . $cliIndex);
require_once __DIR__ . '/../../vendor/phpunit/phpunit/PHPUnit/Framework/Assert/Functions.php';
