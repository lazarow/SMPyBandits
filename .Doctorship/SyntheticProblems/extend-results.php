<?php
require __DIR__ . '/_common.php';
require __DIR__ . '/classes/Statistics.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plots = [];
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : ceil(5 * array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    foreach ($experiment['policies'] as $idx => $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        $experimentDir = $configuration['output.dir'] . '/experiments/' . $md5;
        if (file_exists($experimentDir . '/raw_data.json.tar.gz') === false) { // lloking for compressed file
            exit('[!] The experiment does not have raw data for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.' . PHP_EOL);
        }
        // Decompression of RAW data
        shell_exec('tar -zcvf "' . $experimentDir . '/raw_data.json.tar.gz"');
        $results = json_decode(file_get_contents($experimentDir . '/results.json'), true);
        if (
            isset($results['regret'])
            && isset($results['bestArmPulls'])
            && isset($results['armsPulls'])
            && array_key_exists('force', $options) === false
        ) {
            continue;
        }
        $data = [];
        foreach (json_decode(file_get_contents($experimentDir . '/raw_data.json'), true)['datasets'] as $dataset) {
            $data[$dataset['alias'][0]] = $dataset['value'][0];
        }
        unlink($experimentDir . '/raw_data.json'); // removing decompressed file
        // Fix regrets
        $results['regret'] = [
            'min' => min($data['/env_0/regrets']),
            'mean' => Statistics::average($data['/env_0/regrets']),
            'median' => Statistics::median($data['/env_0/regrets']),
            'max' => max($data['/env_0/regrets']),
            'st.dev' => Statistics::stdDev($data['/env_0/regrets']),
            'q25' => Statistics::quartile25($data['/env_0/regrets']),
            'q75' => Statistics::quartile75($data['/env_0/regrets'])
        ];
        // Add extra results values for figures
        $results['bestArmPulls'] = [
            'min' => min($data['/env_0/lastPulls'][0]) / $h,
            'mean' => Statistics::average($data['/env_0/lastPulls'][0]) / $h,
            'median' => Statistics::median($data['/env_0/lastPulls'][0]) / $h,
            'max' => max($data['/env_0/lastPulls'][0]) / $h,
            'st.dev' => Statistics::stdDev($data['/env_0/lastPulls'][0]) / $h
        ];
        for ($arm = 0; $arm < $nofArms; ++$arm) {
            $results['armsPulls'][$arm] = [
                'min' => min($data['/env_0/lastPulls'][$arm]),
                'mean' => Statistics::average($data['/env_0/lastPulls'][$arm]),
                'median' => Statistics::median($data['/env_0/lastPulls'][$arm]),
                'max' => max($data['/env_0/lastPulls'][$arm]),
                'st.dev' => Statistics::stdDev($data['/env_0/lastPulls'][$arm])
            ];
        }
        file_put_contents($experimentDir . '/results.json', json_encode($results, JSON_PRETTY_PRINT));
        echo '[i] The results for the experiment: ' . $md5 . ' has been fixed.' . PHP_EOL;
    }
}
echo '[i] The results have been fixed.' . PHP_EOL;
