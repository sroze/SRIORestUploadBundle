<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_file($loaderFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $loaderFile;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$testEnv = isset($_SERVER['TEST_ENV']) ? $_SERVER['TEST_ENV'] : 'gaufrette';
$message = "Currently testing filesystem layer: \"$testEnv\" (options: Gaufrette, Flysystem. see 'test.sh' script)";
if (PHP_SAPI === 'cli') {
    echo "\e[48;5;202m$message\e[49m\r\n\r\n";
} else {
    echo $message;
}
