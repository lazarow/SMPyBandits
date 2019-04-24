<?php
require __DIR__ . '/_common.php';
$configuration = getConfiguration();

if (file_exists($configuration['queue.filepath']) === false) {
    exit('[!] The queue file is not generated');
}

$rawQueue = file_get_contents($configuration['queue.filepath']);
$md5 = md5($rawQueue);
$standingsDir = $configuration['output.dir'] . '/standings/' . $md5;
if (array_key_exists('force', $options) === false && file_exists($standingsDir) && file_exists($standingsDir . '/standings.end')) {
    exit('[_] The standings of the experiments already exists');
}
@mkdir($standingsDir);
$queue = json_decode($rawQueue, true);
$results = [];
$repetition = null;
$arms = null;
for ($i = 0; $i < count($queue); ++$i) {
    $experiment = $queue[$i];
    $repetitions = $experiment['repetitions'];
    $arms = $experiment['arms'];
    $experimentDir = $configuration['output.dir'] . '/' . $experiment['md5'];
    if (file_exists($experimentDir . '/results.json') === false) {
        exit('[!] The queued experiments no. ' . $i . ' does not have results.');
    }
    $results[] = json_decode(file_get_contents($experimentDir . '/results.json'), true);
}
var_dump($results);
$standings = [
    'regret' => array_keys($results),
    'time' => array_keys($results),
    'memory' => array_keys($results)
];
function cmp($val1, $val2) {
    return $val1 == $val2 ? 0 : ($val1 < $val2 ? -1 : 1);
}
uasort($standings['regret'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['regret']['mean'], $results[$idx2]['regret']['mean']);
});
uasort($standings['time'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['time']['mean'], $results[$idx2]['time']['mean']);
});
uasort($standings['memory'], function ($idx1, $idx2) use ($results) {
    return cmp($results[$idx1]['memory']['mean'], $results[$idx2]['memory']['mean']);
});
$shortMd5 = substr($md5, -5, 5);
# <editor-fold defaultstate="collapsed" desc="Latex Table Generation">
$latexTable = '\\begin{table}[!ht]
\\begin{minipage}{\\textwidth}\\begin{center}
\\caption{Uśrednione wyniki po ' . $repetitions . ' powtórzeniach posortowane wg. całkowitej straty, dla problemu: [ ';
for ($i = 0; $i < count($arms); ++$i) {
    $latexTable .= ($i === 0 ? '{\boldmath' : '; ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$' . ($i === 0 ? '}' : '');
}
$latexTable .= ' ], liczba ramion $K{=}' . count($arms) . '$.}
\\label{table:' . $shortMd5 . '}
\\rowcolors{4}{white}{lightgray1}
\\begin{tabular}{cp{170pt}rrrrrr}
    \\hline
    \\multirow{2}{*}[-0.7ex]{\#}
    & \\multirow{2}{*}[-0.7ex]{\\centering Algorytm strategii wyboru}
    & \\multicolumn{2}{|m{70pt}}{\\centering \vspace{2pt} Całkowita oczekiwana strata}
    & \\multicolumn{2}{|m{70pt}}{\\centering Czas wykonania}
    & \\multicolumn{2}{|m{60pt}}{\\centering Zużyta pamięć}
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
file_put_contents($standingsDir . '/table_' . $shortMd5 . '.tex', $latexTable);
# </editor-fold>
file_put_contents($standingsDir . '/standings.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($standingsDir) . PHP_EOL;
