<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();

if (file_exists($configuration['queue.filepath']) === false) {
    exit('[!] The queue file is not generated');
}

$rawQueue = file_get_contents($configuration['queue.filepath']);
$md5 = '_' . md5($rawQueue);
$experimentDir = $configuration['output.dir'] . '/' . $md5;
if (array_key_exists('force', $options) === false && file_exists($experimentDir) && file_exists($experimentDir . '/standings.end')) {
    exit('[_] The standings of the experiments already exists');
}
$queue = json_decode($rawQueue, true);
$results = [];
for ($i = 0; $i < count($queue); ++$i) {
    $experiment = $queue[$i];
    $experimentDir = $configuration['output.dir'] . '/' . $experiment['md5'];
    if (file_exists($experimentDir . '/results.json') === false) {
        exit('[!] The queued experiments no. ' . $i . ' does not have results.');
    }
    $results[] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
}
$standings = [
    'regret' => array_keys($results),
    'time' => array_keys($results),
    'memory' => array_keys($results)
];
function cmp($val1, $val2) {
    return $val1 == $val2 ? 0 : ($val1 < $val2 ? -1 : 1);
}
uasort($standings['regret'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['regret']['mean'], $results[$idx2]['regret']['mean']);
});
uasort($standings['time'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['time']['mean'], $results[$idx2]['time']['mean']);
});
uasort($standings['memory'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['memory']['mean'], $results[$idx2]['memory']['mean']);
});

