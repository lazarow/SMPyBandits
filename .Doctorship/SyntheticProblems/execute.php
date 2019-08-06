<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();

if (file_exists($configuration['queue.filepath']) === false) {
    exit('[!] The queue file is not generated');
}

$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$queue = json_decode(file_get_contents($configuration['queue.filepath']), true);
for ($i = 0; $i < count($queue); ++$i) {
    $experiment = $queue[$i];
    $experimentDir = $configuration['output.dir'] . '/' . $experiment['md5'];
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
    $h = count($experiment['arms']) < 3 ? 200 : 3 * ceil(array_reduce(array_slice($experiment['arms'], 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    $experiment['h'] = $h;
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
    // Conducting the experiment
    $smpybanditsOutput = utf8_encode(shell_exec(($isWin ? 'set NOPLOTS=True&& ' : 'NOPLOTS=True ') . 'python ' . $configuration['smpybandits.dir'] . '/main.py configuration_experiment'));
    file_put_contents($experimentDir . '/smpybandits_output.txt', $smpybanditsOutput);
    // Copying the HDF5 file (raw data)
    $plotsDir = __DIR__ . '/plots/SP__K' . count($experiment['arms']) . '_T' . $h . '_N' . $experiment['repetitions'] . '__1_algos';
    foreach (glob($plotsDir . '/*.hdf5') as $filename) {
        shell_exec($configuration['h5tojson'] . ' "' . $filename . '" > "' . $experimentDir . '/raw_data.json"');
    }
    // Removing SMPyBandits plots folder
    if (file_exists($plotsDir)) {
        deleteDir($plotsDir);
    }
    // Retrieving the summary of the experiment
    $regretPattern = "/\s+For policy #([0-9]+) called '(.*?)' \.\.\.\s+Last regrets \(for all repetitions\) have:\sMin of\s+last regrets R_T = ([0-9.\-e\+]+)\sMean of\s+last regrets R_T = ([0-9.\-e\+]+)\sMedian of last regrets\s+R_T = ([0-9.\-e\+]+)\sMax of\s+last regrets R_T = ([0-9.\-e\+]+)\sStandard deviation\s+R_T = ([0-9.\-e\+]+)/mi";
    $regretMatches = [];
    preg_match_all($regretPattern, $smpybanditsOutput, $regretMatches);
    foreach (array_keys($regretMatches[0]) as $idx) {
        $experiment['policy']['name'] = trim($regretMatches[2][$idx]);
        $experiment['regret']['min'] = (float) $regretMatches[3][$idx];
        $experiment['regret']['mean'] = (float) $regretMatches[4][$idx];
        $experiment['regret']['median'] = (float) $regretMatches[5][$idx];
        $experiment['regret']['max'] = (float) $regretMatches[6][$idx];
        $experiment['regret']['st.dev'] = (float) $regretMatches[7][$idx];
    }
    $timePattern = "/For policy #[0-9]+ called '(.*)' \.\.\.\s+([0-9\.-e\+]+) [±Â]+? ([0-9\.-e]+)/mi";
    $timeMatches = [];
    preg_match_all($timePattern, $smpybanditsOutput, $timeMatches);
    foreach (array_keys($timeMatches[0]) as $idx) {
        $experiment['time']['mean'] = (float) $timeMatches[2][$idx];
        $experiment['time']['st.dev'] = (float) $timeMatches[3][$idx];
    }
    $memoryPattern = "/For policy #[0-9]+ called '(.*)' \.\.\.\s+([0-9\.-e]+) (B|KiB|MiB) [±Â]+? ([0-9\.-e]+)/mi";
    $memoryMatches = [];
    preg_match_all($memoryPattern, $smpybanditsOutput, $memoryMatches);
    foreach (array_keys($memoryMatches[0]) as $idx) {
        $experiment['memory']['mean'] = (float) $memoryMatches[2][$idx] * ($memoryMatches[3][$idx] == 'B' ? 1 : ($memoryMatches[3][$idx] == 'KiB' ? 1024 : 1000 * 1024));
        $experiment['memory']['st.dev'] = (float) $memoryMatches[4][$idx] * ($memoryMatches[3][$idx] == 'B' ? 1 : ($memoryMatches[3][$idx] == 'KiB' ? 1024 : 1000 * 1024));
    }
    // Saving the experiment
    file_put_contents($experimentDir . '/results.json', json_encode($experiment, JSON_PRETTY_PRINT));
    // Removing the SMPyBandits configuration file
    unlink($configuration['smpybandits.dir'] . '/configuration_experiment.py');
    // Conducting the experiment END
    echo '[i] The experiment no. ' . $i . ' has been conducted' . PHP_EOL;
    $elapsed = microtime(true) - $start;
    echo '[_] The experiment no. ' . $i . ' time in seconds: ' . $elapsed . PHP_EOL;
    file_put_contents($experimentDir . '/experiment.end', 'done');
}
echo '[i] All experiments have been conducted.' . PHP_EOL;
