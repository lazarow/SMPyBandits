<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$plotsDir = $configuration['output.dir'] . '/plots/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($plotsDir) && file_exists($plotsDir . '/plots.end')) {
    exit('[_] The plots of the experiments already exists' . PHP_EOL);
}
@mkdir($plotsDir);
function cmp($val1, $val2) {
    return $val1 == $val2 ? 0 : ($val1 < $val2 ? -1 : 1);
}
$latexAllPlots = '';
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
        $policies[$idx] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
    }
    
    $standings = [
        'regret' => array_keys($policies),
        'time' => array_keys($policies),
        'memory' => array_keys($policies),
        'bestArmPulls' => array_keys($policies)
    ];
    usort($standings['regret'], function ($idx1, $idx2) use ($policies) {
        return cmp($policies[$idx1]['regret']['mean'], $policies[$idx2]['regret']['mean']);
    });
    usort($standings['time'], function ($idx1, $idx2) use ($policies) {
        return cmp($policies[$idx1]['time']['mean'], $policies[$idx2]['time']['mean']);
    });
    usort($standings['memory'], function ($idx1, $idx2) use ($policies) {
        return cmp($policies[$idx1]['memory']['mean'], $policies[$idx2]['memory']['mean']);
    });
    usort($standings['bestArmPulls'], function ($idx1, $idx2) use ($policies) {
        return cmp($policies[$idx2]['bestArmPulls']['mean'], $policies[$idx1]['bestArmPulls']['mean']);
    });
    
    $standings['regret'] = array_slice($standings['regret'], 0, 10);
    $standings['time'] = array_slice($standings['time'], 0, 10);
    $standings['memory'] = array_slice($standings['memory'], 0, 10);
    $standings['bestArmPulls'] = array_slice($standings['bestArmPulls'], 0, 10);
    
    foreach ($standings['regret'] as $idx) {
        $experimentDir = $configuration['output.dir'] . '/experiments/' . md5(json_encode($experiment['policies'][$idx]) . json_encode($arms) . $experiment['repetitions']);
        $results[$idx] = [];
        foreach (json_decode(file_get_contents($experimentDir . '/raw_data.json'), true)['datasets'] as $dataset) {
            $results[$idx][$dataset['alias'][0]] = $dataset['value'][0];
        }
        $points[$idx] = array_fill(0, $h, 0);
        for ($i = 0; $i < $h; ++$i) {
            for ($j = 0; $j < $experiment['repetitions']; ++$j) {
                $points[$idx][$i] += $results[$idx]['/env_0/culmulativeRegrets'][$j][$i];
            }
            $points[$idx][$i] /= $experiment['repetitions'];
        }
        $size = max(1, floor(count($points[$idx]) / 400));
        for ($i = 0; $i < $h; ++$i) {
            if ($i % $size !== 0 && $i !== $h - 1) {
                unset($points[$idx][$i]);
            }
        }
    }
    
    $latexFigure = '\begin{figure}
    \centering
    \includegraphics[width=\textwidth, angle=0]{' . $experiment['name'] . '/regret' . $md5 . '.pdf}
    \caption{Wykres uśrednionej całkowitej straty, liczoną wg. następującego wzoru: $ R{=} \sum_{t=1}^{H} ( \max\limits_{i=1,\ldots, K} \mathbb{E} \lbrack x_{i,t} \rbrack ) - \sum_{t=1}^{H} \mathbb{E} \lbrack x_{S_t,t} \rbrack $, w kolejnych iteracjach dla problemu: $\{$';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexFigure .= ($i === 0 ? '' : ', ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$';
    }
    $latexFigure .= '$\}$ z liczbą ramion $K{=}' . count($arms) . '$ oraz skończoną liczbą iteracji $ H {=} ' . $h . '$.'
        . ($nofPolicies > 10 ? ' Wykres przedstawia 10 najlepszych wyników z ' . $nofPolicies . '.' : '') . '}
\label{fig:regret' . $md5 . '}
\end{figure}';
    file_put_contents($plotsDir . '/regret' . $md5 . '.tex', $latexFigure);
    $latexAllPlots .= $latexFigure;
    
    $nofDataPoints = count($points[$standings['regret'][0]]);
    $plots['regret' . $md5] = '\\begin{tikzpicture}
\\begin{axis}[
    width=16.49cm,
    height=10cm,
    legend entries = {' . array_reduce($standings['regret'], function ($carry, $policyIdx) use ($policies) {
        return $carry . strtr($policies[$policyIdx]['policy']['name'], ['='=>'{=}']) . '\\\\';
    }, '') . '},
    xlabel={Krok czasowy $t$},
    xticklabel style={/pgf/number format/1000 sep=},
    ylabel={Uśredniona całkowita strata},
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
    foreach ($standings['regret'] as $policyIdx) {
        $t = 0;
        $plots['regret' . $md5] .= '\\addplot coordinates {
    ' . implode('', array_map(function ($value, $t) {
        return '(' . $t .',' . $value . ')';
    }, $points[$policyIdx], array_keys($points[$policyIdx]))) . '
};
';
    }
    $plots['regret' . $md5] .= '\\end{axis}
\\end{tikzpicture}';
    
}

// single plots
$template = file_get_contents($configuration['plot.template.filepath']);
foreach ($plots as $id => $plot) {
    $texPlotPath = $plotsDir . '/' . $id . '_plot_data.tex';
    file_put_contents($texPlotPath, strtr($template, ['{{tikzpicture}}' => $plot]));
    shell_exec('pdflatex -interaction=nonstopmode -shell-escape ' . realpath($texPlotPath));
    echo '[_] The plot: ' . $id . ' has been generated' . PHP_EOL;
    foreach (glob($id . '*') as $filename) {
        if (strpos($filename, '.pdf') !== false) {
            copy($filename, $plotsDir . '/' . $id . '.pdf');
        }
        unlink($filename);
    }
}

// all plots
$template = file_get_contents($configuration['tables.template.filepath']);
file_put_contents($plotsDir . '/all_plots.tex', strtr($template, ['{{tables}}' => $latexAllPlots]));
$out = shell_exec('pdflatex -interaction=nonstopmode -shell-escape ' . realpath($plotsDir . '/all_plots.tex'));
foreach (glob('all_plots*') as $filename) {
    if (strpos($filename, '.pdf') !== false) {
        copy($filename, $plotsDir . '/all_plots.pdf');
    }
    unlink($filename);
}

file_put_contents($plotsDir . '/plots.end', 'done');
echo '[i] The plots have been generated in the path: ' . realpath($plotsDir) . PHP_EOL;
