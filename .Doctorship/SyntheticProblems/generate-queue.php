<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$queue = [];
foreach ($experiment['arms'] as $arms) {
    $k = count($arms);
    foreach ($experiment['policies'] as $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        $queue[] = [
            'policy' => $policy,
            'arms' => $arms,
            'repetitions' => $experiment['repetitions'],
            'md5' => $md5
        ];
    }
}
file_put_contents($configuration['queue.filepath'], json_encode($queue));
echo '[i] The queue file has been generated in the path: ' . realpath($configuration['queue.filepath']) . PHP_EOL;
