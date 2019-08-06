<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
echo '[i] MD5' . PHP_EOL;
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    $nofPolicies = count($experiment['policies']);
    echo '[_] The number of arms: ' . $nofArms . PHP_EOL;
    echo '[_] Arms: ' . implode('; ', $arms) . PHP_EOL; 
    echo '[_] The number of repetitions: ' . $experiment['repetitions'] . PHP_EOL; 
    foreach ($experiment['policies'] as $idx => $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        echo 'The MD5 of ' . $policy['archtype'] . ': ' . $md5 . PHP_EOL;
    }
}
