<?php

use PHPPeru\Examples;

require __DIR__.'/vendor/autoload.php';

$examples = new Examples();

$exampleNames = array(
    'nastyExample',
    'goodExample',
    'goodAndFastExample',
    'goodAndFastAndAutomaticExample',
    'pimpleRefactorExample'
);

timeExample($exampleNames, $examples);

function timeExample($methodNames, $examples) {
    foreach ($methodNames as $methodName) {
        $start = microtime(true);
        call_user_func(array($examples, $methodName));
        echo $methodName . '........ ' . (microtime(true) - $start) . "\n";
    }
}