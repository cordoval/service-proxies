<?php

use PHPPeru\Example;

require __DIR__.'/vendor/autoload.php';

$example = new Example();

$start = microtime(true);
$example->nastyExample();
echo microtime(true) - $start;

echo "\n";

$start = microtime(true);
$example->goodExample();
echo microtime(true) - $start;

