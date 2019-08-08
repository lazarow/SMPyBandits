<?php
require __DIR__ . '/_common.php';
require __DIR__ . '/classes/Statistics.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plots = [];
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    foreach ($experiment['policies'] as $idx => $policy) {
        $experimentDir = $configuration['output.dir'] . '/experiments/' . md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        if (file_exists($experimentDir . '/raw_data.json') === false) {
            exit('[!] The experiment does not have raw data for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.' . PHP_EOL);
        }
        $data = [];
        foreach (json_decode(file_get_contents($experimentDir . '/raw_data.json'), true)['datasets'] as $dataset) {
            $data[$dataset['alias'][0]] = $dataset['value'][0];
        }
        $results = json_decode(file_get_contents($experimentDir . '/results.json'), true);
        // Add extra results values for figures
        $results['bestArmPulls'] = [
            'min' => min($data['/env_0/lastPulls'][0]),
            'mean' => Statistics::average($data['/env_0/lastPulls'][0]),
            'median' => Statistics::median($data['/env_0/lastPulls'][0]),
            'max' => max($data['/env_0/lastPulls'][0]),
            'st.dev' => Statistics::stdDev($data['/env_0/lastPulls'][0])
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
    }
}
echo '[i] The results have been fixed.' . PHP_EOL;
