<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();

if (file_exists($configuration['queue.filepath']) === false) {
    exit('[!] The queue file is not generated');
}

$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$queue = json_decode(file_get_contents($configuration['queue.filepath']), true);
$results = [];
for ($i = 0; $i < count($queue); ++$i) {
    $experiment = $queue[$i];
    $experimentDir = $configuration['output.dir'] . '/' . $experiment['md5'];
    if (file_exists($experimentDir . '/results.json') === false) {
        exit('[!] The queued experiments no. ' . $i . ' does not have results.');
    }
    $results[] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
}
