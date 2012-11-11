<?php

use PHPPeru\Examples;

require __DIR__.'/vendor/autoload.php';

$examples = new Examples();
/*
$start = microtime(true);
$examples->nastyExample();
echo 'nasty ........ ' . (microtime(true) - $start) . "\n";

$start = microtime(true);
$examples->goodExample();
echo 'good ........ ' . (microtime(true) - $start) . "\n";

$start = microtime(true);
$examples->goodAndFastExample();
echo 'good and fast ... ' . (microtime(true) - $start) . "\n";

$start = microtime(true);
$examples->goodAndFastAndAutomaticExample();
echo 'good and fast and automatic ' . (microtime(true) - $start) . "\n";
*/
$start = microtime(true);
$examples->pimpleRefactorExample();
echo 'pimple refactor ' . (microtime(true) - $start) . "\n";
