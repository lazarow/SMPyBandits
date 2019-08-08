<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();
$experiment = getExperiment();
$standingsDir = $configuration['output.dir'] . '/standings/' . $experiment['name'];
if (array_key_exists('force', $options) === false && file_exists($standingsDir) && file_exists($standingsDir . '/standings.end')) {
    exit('[_] The standings of the experiments already exists' . PHP_EOL);
}
@mkdir($standingsDir);
function cmp($val1, $val2) {
    return $val1 == $val2 ? 0 : ($val1 < $val2 ? -1 : 1);
}
$repetitions = $experiment['repetitions'];
$arms = $experiment['arms'];
$summativeStandings = [];
$policies = [];
foreach ($experiment['policies'] as $idx => $policy) {
    $summativeStandings[$idx] = [
        'regret' => [],
        'time' => [],
        'memory' => []
    ];
    $policies[$idx] = '';
}
$allLatexTables = '';
foreach ($experiment['arms'] as $arms) {
    $k = count($arms);
    $experimentMd5 = substr(md5(json_encode($arms)), -7, 7);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : 3 * ceil(array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
        return $carry + 1 / pow($bestArm - $arm, 2);
    }, 0));
    $results = [];
    foreach ($experiment['policies'] as $idx => $policy) {
        $md5 = md5(json_encode($policy) . json_encode($arms) . $experiment['repetitions']);
        $experimentDir = $configuration['output.dir'] . '/experiments/' . $md5;
        if (file_exists($experimentDir . '/results.json') === false) {
            exit('[!] The experiment does not have results for policy: ' . $policy['archtype'] . ' and arms: ' . implode('; ', $arms) . '.' . PHP_EOL);
        }
        $results[$idx] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
        $policies[$idx] = $results[$idx]['policy']['name'];
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
\\caption{Uśrednione po ' . $repetitions . ' powtórzeniach wyniki eksperymentu posortowane wg. całkowitej straty dla problemu: ';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexTable .= ($i === 0 ? '' : ', ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$';
    }
    $latexTable .= ' z liczbą ramion $K{=}' . count($arms) . '$ oraz skończoną liczbą iteracji $ H {=} ' . $h . '$.}
\\label{table:' . $experimentMd5 . '}
\\rowcolors{4}{white}{lightgray1}
\\begin{tabular}{cp{155pt}rrrrrr}
    \\hline
    \\multirow{2}{*}[-0.6ex]{\#}
    & \\multirow{2}{*}[-0.6ex]{\\centering Algorytm strategii wyboru}
    & \\multicolumn{2}{|m{90pt}}{\\centering \vspace{2pt} \footnotesize Całkowita uzyskana strata}
    & \\multicolumn{2}{|m{80pt}}{\\centering \footnotesize Czas wykonania ($s$)}
    & \\multicolumn{2}{|m{70pt}}{\\centering \footnotesize Zajęta pamięć ($B$)}
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
        $latexTable .= '    \footnotesize $' . ($i + 1) . '$ & \footnotesize ' . strtr($result['policy']['name'], ['=' => '{=}'])
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
    $allLatexTables .=  $latexTable;
    # </editor-fold>
    # <editor-fold defaultstate="collapsed" desc="Gather The Summative Standings">
    foreach (['regret', 'time', 'memory'] as $metrics) {
        for ($i = 0; $i < count($standings[$metrics]); ++$i) {
            $summativeStandings[$standings[$metrics][$i]][$metrics][] = ($i + 1);
        }
    }
    # </editor-fold>
}
# <editor-fold defaultstate="collapsed" desc="The Latex Summative Standings Table">
$nofProblems = count($experiment['arms']);
$nofPolicies = count($experiment['policies']);
$metricsLabels = [
    'regret' => 'całkowitą uzyskaną stratę',
    'time' => 'czas wykonania',
    'memory' => 'zajętą pamięć'
]; 
foreach (['regret', 'time', 'memory'] as $metrics) {
    foreach (array_keys($experiment['policies']) as $idx) {
        $summativeStandings[$idx][$metrics][] = number_format(array_reduce($summativeStandings[$idx][$metrics], function ($carry, $place) use ($nofProblems, $nofPolicies) {
            return $carry + 1/$nofProblems * (1 - ($place - 1) / ($nofPolicies - 1));
        }, 0), 2, '{,}', '');
        $summativeStandings[$idx][$metrics] = array_map(function ($value) {
            return ($value === 1 ? '\\boldmath' : '') . '$' . $value . '$';
        }, $summativeStandings[$idx][$metrics]);
    }
    uasort($summativeStandings, function ($policy1, $policy2) use ($metrics, $nofProblems) {
        return cmp($policy2[$metrics][$nofProblems], $policy1[$metrics][$nofProblems]);
    });
    $latexTable = '\\begin{table}[!ht]
\\begin{minipage}{\\textwidth}\\begin{center}
\\caption{Zestawienie wyników, w którym algorytmy strategii wyboru zostały posortowane wg. wartości ważonej funkcji kompromisu wyliczonej na podstawie 
miejsc w rankingach dla założonych w eksperymencie równoważnych problemów ze względu na ' . $metricsLabels[$metrics] . '.}
\\label{table:' . $experiment['name'] . '_' . $metrics . '}
\\begin{tabularx}{\\textwidth}{cX' . str_repeat('c', $nofProblems) . 'r}
\\hline
    \\# & Algorytm strategii wyboru';
    for ($i = 1; $i <= $nofProblems; ++$i) {
        $latexTable .= ' & \footnotesize ' . $i . '$^\star$';
    }
    $latexTable .= ' & \footnotesize $A^{WSM}$$^{\star\star}$ \\\\
\\hline \\hline
';
    $place = 1;
    foreach ($summativeStandings as $policyIdx => $results) {
        $latexTable .= '\rowcolor{' . ($place % 2 == 1 ? 'white' : 'lightgray1') . '}
';
        $latexTable .= '\footnotesize $' . $place++ . '$ & \footnotesize ' . strtr($policies[$policyIdx], ['=' => '{=}']) . ' & \footnotesize ' . implode(' & \footnotesize ', $results[$metrics]) . ' \\\\
';
    }
    $latexTable .= '\\hline
\\multicolumn{' . ($nofProblems + 3) . '}{@{}l}{\footnotesize $^\star$ Numer problemu, dane w tabeli oznaczają miejsce w rankingu.} \\\\
\\multicolumn{' . ($nofProblems + 3) . '}{@{}l}{\footnotesize $^{\star\star}$ $A^{WSM}{=}\sum\nolimits_{j=1}^{' . $nofProblems . '} (1{/}' . $nofProblems . ') (1 - (a_{ij} {-} 1) {/} ' . ($nofPolicies - 1) . ')$ gdzie $ a_{ij} $ to miejsce zajęte przez $ i $-tą strategię w $ j $-tym problemie.}
\\end{tabularx}
\\end{center}\\end{minipage}
\\end{table}';
    file_put_contents($standingsDir . '/table_' . $metrics . '.tex', $latexTable);
    $allLatexTables .= $latexTable;
}
# </editor-fold>
$template = file_get_contents($configuration['tables.template.filepath']);
file_put_contents($standingsDir . '/all_tables.tex', strtr($template, ['{{tables}}' => $allLatexTables]));
shell_exec('pdflatex -interaction=nonstopmode -shell-escape ' . realpath($standingsDir . '/all_tables.tex'));
foreach (glob('all_tables*') as $filename) {
    if (strpos($filename, '.pdf') !== false) {
        copy($filename, $standingsDir . '/all_tables.pdf');
    }
    unlink($filename);
}
file_put_contents($standingsDir . '/standings.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($standingsDir) . PHP_EOL;
