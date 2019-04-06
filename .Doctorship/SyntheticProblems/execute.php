<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();

if (file_exists($configuration['queue.filepath']) === false) {
    exit('[!] The queue file is not generated');
}

$queue = json_decode(file_get_contents($configuration['queue.filepath']), true);
for ($i = 0; $i < count($queue); ++$i) {
    $experiment = $queue[$i];
    $experimentDir = $configuration['findings.dir'] . '/' . $experiment['md5'];
    if (array_key_exists('force', $options) === false && file_exists($experimentDir) && file_exists($experimentDir . '/experiment.end')) {
        echo '[_] The findings of the experiments no. ' . $i . ' already exists' . PHP_EOL;
        continue;
    }
    @mkdir($experimentDir);
    // Conducting the experiment BEGIN
    $start = microtime(true);
    echo '[i] The experiment no. ' . $i . ' has been started' . PHP_EOL;
    // Calculating H-value
    $bestArm = $experiment['arms'][0];
    $h = count($experiment['arms']) < 3 ? 200 : ceil(array_reduce(array_slice($experiment['arms'], 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    // Generating the SMPyBandits configuration file
    $configurationTemplate = file_get_contents($configuration['configuration.template.filepath']);
    $params = '';
    foreach ($experiment['policy']['params'] as $name => $value) {
        $params .= (strlen($params) ? ', ' : '') . '"' . $name . '": ' . (strpos($value, 'str:') === false ? $value : '"' . strtr($value, ['str:' => '']) . '"');
    }
    file_put_contents($experimentDir . '/configuration_experiment.py', strtr($configurationTemplate, [
        '{{HORIZON}}' => $h,
        '{{REPETITIONS}}' => $experiment['repetitions'],
        '{{N_JOBS}}' => 8,
        '{{ARMS}}' => implode(', ', $experiment['arms']),
        '{{POLICY}}' => '{"archtype": ' . $experiment['policy']['archtype'] . ', "params": {' . $params . '}}'
    ]));
    // Copying the SMPyBandits configuration file
    copy($experimentDir . '/configuration_experiment.py', $configuration['smpybandits.dir'] . '/configuration_experiment.py');
    
    // ...
    
    // Removing the SMPyBandits configuration file
    unlink($configuration['smpybandits.dir'] . '/configuration_experiment.py');
    // Conducting the experiment END
    echo '[i] The experiment no. ' . $i . ' has been conducted' . PHP_EOL;
    $elapsed = microtime(true) - $start;
    echo '[_] The experiment no. ' . $i . ' time in seconds: ' . $elapsed . PHP_EOL;
    file_put_contents($configuration['findings.dir'] . '/' . $experiment['md5'] . '/experiment.end', 'done');
}
