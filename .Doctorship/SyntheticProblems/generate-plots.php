<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plotsDir = $configuration['output.dir'] . '/plots/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($plotsDir) && file_exists($plotsDir . '/plots.end')) {
    exit('[_] The plots of the experiments already exists' . PHP_EOL);
}
@mkdir($plotsDir);
$plots = [];
foreach ($experiment['arms'] as $arms) {
    $nofArms = count($arms);
    $nofPolicies = count($experiment['policies']);
    $md5 = substr(md5(json_encode($arms)), -7, 7);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : ceil(5 * array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    $results = $policies = $points = [];
    foreach ($experiment['policies'] as $idx => $policy) {
        $experimentDir = $configuration['output.dir'] . '/experiments/' . md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        if (file_exists($experimentDir . '/raw_data.json') === false) {
            exit('[!] The experiment does not have raw data for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.' . PHP_EOL);
        }
        $results[$idx] = [];
        foreach (json_decode(file_get_contents($experimentDir . '/raw_data.json'), true)['datasets'] as $dataset) {
            $results[$idx][$dataset['alias'][0]] = $dataset['value'][0];
        }
        $policies[$idx] = json_decode(file_get_contents($experimentDir . '/results.json'), true)['policy']['name'];
        $points[$idx] = array_fill(0, $h, 0);
        for ($i = 0; $i < $h; ++$i) {
            for ($j = 0; $j < $experiment['repetitions']; ++$j) {
                $points[$idx][$i] += $results[$idx]['/env_0/culmulativeRegrets'][$j][$i];
            }
            $points[$idx][$i] /= $experiment['repetitions'];
        }
    }
    # <editor-fold defaultstate="collapsed" desc="Regret Plot">
    $nofDataPoints = $h;
    $plots['regret' . $md5] = '\\begin{tikzpicture}
\\begin{axis}[
    width=16.5cm,
    height=10cm,
    legend entries = {' . array_reduce($policies, function ($carry, $name) {
        return $carry . strtr($name, ['='=>'{=}']) . '\\\\';
    }, '') . '},
    label style={font=\footnotesize},
    xlabel={Krok czasowy $t$},
    xticklabel style={/pgf/number format/1000 sep=},
    ylabel={Całkowita uzyskana strata $ R{=} \sum_{t=1}^{H} ( \max\limits_{i=1,\ldots, K} \mathbb{E} \lbrack x_{i,t} \rbrack ) - \sum_{t=1}^{H} \mathbb{E} \lbrack x_{S_t,t} \rbrack $},
    xmin=0, xmax=' . $h . ',
    ymin=0, ymax=' . max(array_map(function ($data) { return max($data['/env_0/cumulatedRegret']); }, $results)) . ',
    legend pos=north west,
    legend cell align={left},
    ymajorgrids=true,
    grid style=dashed,
    mark repeat=' . floor($nofDataPoints / 15) . ',
    mark phase=' . floor($nofDataPoints / 15) . '
]
';
    foreach ($results as $policyIdx => $data) {
        $t = 0;
        $plots['regret' . $md5] .= '\\addplot coordinates {
    ' . implode('', array_map(function ($value) {
        global $t;
        return '(' . $t++ .',' . $value . ')';
    }, $points[$policyIdx])) . '
};
';
    }
    $plots['regret' . $md5] .= '\\end{axis}
\\end{tikzpicture}';
    # </editor-fold>
}
$template = file_get_contents($configuration['plot.template.filepath']);
foreach ($plots as $id => $plot) {
    $texPlotPath = $plotsDir . '/' . $id . '.tex';
    file_put_contents($texPlotPath, strtr($template, ['{{tikzpicture}}' => $plot]));
    shell_exec('pdflatex -interaction=nonstopmode -shell-escape ' . realpath($texPlotPath));
    foreach (glob($id . '*') as $filename) {
        if (strpos($filename, '.pdf') !== false) {
            copy($filename, $plotsDir . '/' . $id . '.pdf');
        }
        unlink($filename);
    }
}
file_put_contents($plotsDir . '/plots.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($plotsDir) . PHP_EOL;
