<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plotsDir = $configuration['output.dir'] . '/plots/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($plotsDir) && file_exists($plotsDir . '/plots.end')) {
    exit('[_] The plots of the experiments already exists');
}
@mkdir($plotsDir);

file_put_contents($plotsDir . '/plots.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($plotsDir) . PHP_EOL;
