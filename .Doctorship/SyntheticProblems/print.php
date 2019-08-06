<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    $nofPolicies = count($experiment['policies']);
    echo '[i] The number of arms: ' . $nofArms . PHP_EOL;
    echo '[i] Arms: ' . implode('; ', $arms) . PHP_EOL; 
    echo '[i] The number of repetitions: ' . $experiment['repetitions'] . PHP_EOL; 
    foreach ($experiment['policies'] as $idx => $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        echo 'The MD5 of ' . $policy['archtype'] . ': ' . $md5 . PHP_EOL;
    }
}
