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
foreach ($experiment['arms'] as $arms) {
    $k = count($arms);
    $experimentMd5 = substr(md5(json_encode($arms)), -7, 7);
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
    uasort($standings['regret'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['regret']['mean'], $results[$idx2]['regret']['mean']);
    });
    uasort($standings['time'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['time']['mean'], $results[$idx2]['time']['mean']);
    });
    uasort($standings['memory'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx1]['memory']['mean'], $results[$idx2]['memory']['mean']);
    });
    # <editor-fold defaultstate="collapsed" desc="Latex Table Generation">
    $latexTable = '\\begin{table}[!ht]
\\begin{minipage}{\\textwidth}\\begin{center}
\\caption{Uśrednione wyniki po ' . $repetitions . ' powtórzeniach posortowane wg. całkowitej straty, dla problemu: [ ';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexTable .= ($i === 0 ? '{\boldmath' : '; ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$' . ($i === 0 ? '}' : '');
    }
    $latexTable .= ' ], liczba ramion $K{=}' . count($arms) . '$.}
\\label{table:' . $experimentMd5 . '}
\\rowcolors{4}{white}{lightgray1}
\\begin{tabular}{cp{170pt}rrrrrr}
    \\hline
    \\multirow{2}{*}[-0.7ex]{\#}
    & \\multirow{2}{*}[-0.7ex]{\\centering Algorytm strategii wyboru}
    & \\multicolumn{2}{|m{70pt}}{\\centering \vspace{2pt} Całkowita oczekiwana strata}
    & \\multicolumn{2}{|m{70pt}}{\\centering Czas wykonania ($s$)}
    & \\multicolumn{2}{|m{60pt}}{\\centering Zużyta pamięć ($B$)}
    \\\\ \\cline{3-8}
    &
    & \\multicolumn{1}{|b{33pt}}{\\centering \\vspace{2pt} $\\bar{x}$} & \\multicolumn{1}{|c}{$SD$}
    & \\multicolumn{1}{|b{33pt}}{\\centering \\vspace{2pt} $\\bar{x}$} & \\multicolumn{1}{|c}{$SD$}
    & \\multicolumn{1}{|b{30pt}}{\\centering \\vspace{2pt} $\\bar{x}$} & \\multicolumn{1}{|c}{$SD$}
    \\\\ \\hline \\hline
';
    for ($i = 0; $i < count($standings['regret']); ++$i) {
        $result = $results[$standings['regret'][$i]];
        $latexTable .= '    ' . ($i + 1) . ' & ' . $result['policy']['name']
            . ' & $' . number_format($result['regret']['mean'], 2, '{,}', '') . '$'
            . ' & $\pm' . number_format($result['regret']['st.dev'], 2, '{,}', '') . '$'
            . ' & $' . number_format($result['time']['mean'], 4, '{,}', '') . '$'
            . ' & $\pm' . number_format($result['time']['st.dev'], 4, '{,}', '') . '$'
            . ' & $' . number_format($result['memory']['mean'], 0, '{,}', '') . '$'
            . ' & $\pm' . number_format($result['memory']['st.dev'], 0, '{,}', '') . '$'
            . '\\\\
';
    }
    $latexTable .= '    \\hline
\\end{tabular}
\\end{center}\\end{minipage}
\\end{table}';
    file_put_contents($standingsDir . '/table_' . $experimentMd5 . '.tex', $latexTable);
    # </editor-fold>
}
file_put_contents($standingsDir . '/standings.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($standingsDir) . PHP_EOL;
