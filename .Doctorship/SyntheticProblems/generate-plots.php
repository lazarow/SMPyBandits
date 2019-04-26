<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plotsDir = $configuration['output.dir'] . '/plots/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($plotsDir) && file_exists($plotsDir . '/plots.end')) {
    exit('[_] The plots of the experiments already exists');
}
@mkdir($plotsDir);
$plots = [];
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    $nofPolicies = count($experiment['policies']);
    $md5 = substr(md5(json_encode($arms)), -7, 7);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : 3 * ceil(array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    $results = $policies = [];
    foreach ($experiment['policies'] as $idx => $policy) {
        $experimentDir = $configuration['output.dir'] . '/' . md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        if (file_exists($experimentDir . '/raw_data.json') === false) {
            exit('[!] The experiment does not have raw data for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.');
        }
        $results[$idx] = [];
        foreach (json_decode(file_get_contents($experimentDir . '/raw_data.json'), true)['datasets'] as $dataset) {
            $results[$idx][$dataset['alias'][0]] = $dataset['value'][0];
        }
        $policies[$idx] = json_decode(file_get_contents($experimentDir . '/results.json'), true)['policy']['name'];
    }
    # <editor-fold defaultstate="collapsed" desc="Regret Plot">
    $plots['regret' . $md5] = '\\begin{tikzpicture}
\\begin{axis}[
    title={XYZ},
    xlabel={X},
    ylabel={Y},
    xmin=0, xmax=' . $h . ',
    ymin=0, ymax=' . max(array_map(function ($results) { return max($results['/env_0/cumulatedRegret']); }, $results)) . ',
    legend pos=north west,
    ymajorgrids=true,
    grid style=dashed,
]
';
    
    $plots['regret' . $md5] .= '\\end{axis}
\\end{tikzpicture}';
    # </editor-fold>
}
$template = file_get_contents($configuration['plot.template.filepath']);
foreach ($plots as $id => $plot) {
    
}
file_put_contents($plotsDir . '/plots.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($plotsDir) . PHP_EOL;
