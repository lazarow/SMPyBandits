<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$standingsDir = $configuration['output.dir'] . '/standings/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($standingsDir) && file_exists($standingsDir . '/standings.end')) {
    exit('[_] The standings of the experiments already exists');
}
@mkdir($standingsDir);
function cmp($val1, $val2) {
    return $val1 == $val2 ? 0 : ($val1 < $val2 ? -1 : 1);
}
$repetitions = $experiment['repetitions'];
$arms = $experiment['arms'];
$summativeStandings = [];
foreach ($experiment['policies'] as $policy) {
    $summativeStandings[] = [
        'regret' => [],
        'time' => [],
        'memory' => []
    ];
}
foreach ($experiment['arms'] as $arms) {
    $k = count($arms);
    $experimentMd5 = substr(md5(json_encode($arms)), -7, 7);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : 3 * ceil(array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    $results = [];
    foreach ($experiment['policies'] as $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        $experimentDir = $configuration['output.dir'] . '/' . $md5;
        if (file_exists($experimentDir . '/results.json') === false) {
            exit('[!] The experiment does not have results for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.');
        }
        $results[] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
    }
    $standings = [
        'regret' => array_keys($results),
        'time' => array_keys($results),
        'memory' => array_keys($results)
    ];
    usort($standings['regret'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['regret']['mean'], $results[$idx2]['regret']['mean']);
    });
    usort($standings['time'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['time']['mean'], $results[$idx2]['time']['mean']);
    });
    usort($standings['memory'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['memory']['mean'], $results[$idx2]['memory']['mean']);
    });
    # <editor-fold defaultstate="collapsed" desc="Latex Table Generation">
    $hasTSPol = $hasUCB = false;
    $limit = isset($options['limit']) ? (int) $options['limit'] : 999;
    $latexTable = '\\begin{table}[!ht]
\\begin{minipage}{\\textwidth}\\begin{center}
\\caption{Uśrednione po ' . $repetitions . ' powtórzeniach wyniki eksperymentu posortowane wg. całkowitej straty dla problemu $ \mathcal{A} {=} \{ $ ';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexTable .= ($i === 0 ? '' : ', ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$';
    }
    $latexTable .= ' $ \} $ z liczbą ramion $K{=}' . count($arms) . '$ oraz skończoną liczbą iteracji $ H {=} ' . $h . '$.}
\\label{table:' . $experimentMd5 . '}
\\rowcolors{4}{white}{lightgray1}
\\begin{tabular}{cp{155pt}rrrrrr}
    \\hline
    \\multirow{2}{*}[-0.6ex]{\#}
    & \\multirow{2}{*}[-0.6ex]{\\centering Algorytm strategii wyboru}
    & \\multicolumn{2}{|m{90pt}}{\\centering \vspace{2pt} \footnotesize Całkowita oczekiwana strata}
    & \\multicolumn{2}{|m{80pt}}{\\centering \footnotesize Czas wykonania ($s$)}
    & \\multicolumn{2}{|m{70pt}}{\\centering \footnotesize Zużyta pamięć ($B$)}
    \\\\ \\cline{3-8}
    &
    & \\multicolumn{1}{|b{45pt}}{\\centering \\vspace{2pt} \footnotesize $\\bar{x}$} & \\multicolumn{1}{|c}{\footnotesize $SD$}
    & \\multicolumn{1}{|b{40pt}}{\\centering \\vspace{2pt} \footnotesize $\\bar{x}$} & \\multicolumn{1}{|c}{\footnotesize $SD$}
    & \\multicolumn{1}{|b{35pt}}{\\centering \\vspace{2pt} \footnotesize $\\bar{x}$} & \\multicolumn{1}{|c}{\footnotesize $SD$}
    \\\\ \\hline \\hline
';
    for ($i = 0; ($limit > 0 || $hasTSPol === false || $hasUCB === false) && $i < count($standings['regret']); ++$i) {
        $idx = $standings['regret'][$i];
        $result = $results[$standings['regret'][$i]];
        if ($result['policy']['archtype'] === 'TSPolP') {
            $hasTSPol = true;
        } else if ($result['policy']['archtype'] === 'UCB') {
            $hasUCB = true;
        } else if ($limit <= 0) {
            continue;
        } else {
            $limit--;
        }
        $latexTable .= '    \footnotesize ' . ($i + 1) . ' & \footnotesize ' . strtr($result['policy']['name'], ['=' => '{=}'])
            . ' & \footnotesize ' . ($standings['regret'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['regret']['mean'], 2, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['regret']['st.dev'], 2, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['time'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['time']['mean'], 4, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['time']['st.dev'], 4, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['memory'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['memory']['mean'], 0, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['memory']['st.dev'], 0, '{,}', '') . '$'
            . '\\\\
';
    }
    $latexTable .= '    \\hline
\\end{tabular}
\\end{center}\\end{minipage}
\\end{table}';
    file_put_contents($standingsDir . '/table_' . $experimentMd5 . '.tex', $latexTable);
    # </editor-fold>
    # <editor-fold defaultstate="collapsed" desc="Gather The Summative Standings">
    foreach (['regret', 'time', 'memory'] as $metrics) {
        for ($i = 0; $i < count($standings[$metrics]); ++$i) {
            $summativeStandings[$standings[$metrics][$i]][$metrics][] = $i;
        }
    }
    # </editor-fold>
}
# <editor-fold defaultstate="collapsed" desc="The Latex Summative Standings Table">
    foreach (['regret', 'time', 'memory'] as $metrics) {
        for ($i = 0; $i < count($standings[$metrics]); ++$i) {
            $summativeStandings[$standings[$metrics][$i]][$metrics][] = $i;
        }
    }
# </editor-fold>
file_put_contents($standingsDir . '/standings.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($standingsDir) . PHP_EOL;
