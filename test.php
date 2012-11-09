<?php

use PHPPeru\Examples;

require __DIR__.'/vendor/autoload.php';

$examples = new Examples();

$start = microtime(true);
$examples->nastyExample();
echo 'nasty ........ ' . (microtime(true) - $start);

echo "\n";

$start = microtime(true);
$examples->goodExample();
echo 'good ........ ' . (microtime(true) - $start);

echo "\n";

$start = microtime(true);
$examples->goodAndFastExample();
echo 'good and fast ... ' . (microtime(true) - $start);

echo "\n";
