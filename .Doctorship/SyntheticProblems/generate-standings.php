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
        'memory' => [],
        'bestArmPulls' => []
    ];
    $policies[$idx] = '';
}
$allLatexTables = '';
foreach ($experiment['arms'] as $arms) {
    $k = count($arms);
    $experimentMd5 = substr(md5(json_encode($arms)), -7, 7);
    $bestArm = $arms[0];
    $h = count($arms) < 3 ? 200 : ceil(5 * array_reduce(array_slice($arms, 1), function ($carry, $arm) use ($bestArm) {
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
        if (
            isset($results[$idx]['regret']) === false
            || isset($results[$idx]['time']) === false
            || isset($results[$idx]['memory']) === false
            || isset($results[$idx]['bestArmPulls']) === false
        ) {
            echo '[!] The data for experiment: ' . $md5 . ' are incomplete.' . PHP_EOL;
        }
    }
    $standings = [
        'regret' => array_keys($results),
        'time' => array_keys($results),
        'memory' => array_keys($results),
        'bestArmPulls' => array_keys($results)
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
    usort($standings['bestArmPulls'], function ($idx1, $idx2) use ($results) {
        return cmp($results[$idx2]['bestArmPulls']['mean'], $results[$idx1]['bestArmPulls']['mean']);
    });
    # <editor-fold defaultstate="collapsed" desc="Latex Table Generation">
    $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
    $latexTable = '\\begin{center}
\\begin{longtable}{|m{4mm}<{\centering}m{60mm}<{\raggedright}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{18mm}<{\raggedleft}m{18mm}<{\raggedleft}|}
\\caption{Uśrednione po ' . $repetitions . ' powtórzeniach wyniki eksperymentu posortowane wg. całkowitej straty dla problemu: $\{$';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexTable .= ($i === 0 ? '' : ', ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$';
    }
    $latexTable .= '$\}$ z liczbą ramion $K{=}' . count($arms) . '$ oraz skończoną liczbą iteracji $ H {=} ' . $h . '$.'
        . (count($standings['regret']) > $limit ? ' Tabela przedstawia ' . $limit . ' najlepszych wyników z ' . count($standings['regret']) . '.' : '') . '}
\\label{tablelong:' . $experimentMd5 . '} \\\\
    
\\hline 
\\multirow{2}{*}[-0.1ex]{\footnotesize \\#}
& \\multirow{2}{*}[-0.1ex]{\footnotesize Algorytm strategii wyboru}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Całkowita strata$^\star$}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Optymalny wybór$^{\star\star}$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize Czas wykonania~($s$)}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize Zajęta pamięć~($B$)} \\\\
\\cline{3-8}
&
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize $\\bar{x}$}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize $\\bar{x}$} \\\\
\\hline 
\\endfirsthead

\\multicolumn{8}{c}{\\footnotesize \\tablename \\thetable{} -- ciąg dalszy z poprzedniej strony} \\\\
\\hline 
\\multirow{2}{*}[-0.1ex]{\footnotesize \\#}
& \\multirow{2}{*}[-0.1ex]{\footnotesize Algorytm strategii wyboru}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Całkowita strata$^\star$}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Optymalny wybór$^{\star\star}$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize Czas wykonania~($s$)}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize Zajęta pamięć~($B$)} \\\\
\\cline{3-8}
&
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize $\\bar{x}$}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize $\\bar{x}$} \\\\
\\hline
\\endhead

\\hline
\\multicolumn{8}{|r|}{\\footnotesize kontynuacja na następnej stronie} \\\\
\\hline
\\endfoot

\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{black}
\\rowcolor{white}
\\multicolumn{8}{l}{\footnotesize $^\star$ Średnia całkowita strata, liczoną wg. następującego wzoru: $ \bar{R}^E{=} \sum_{t=1}^{H} \left( \max\limits_{i=1,\ldots, K} \mathbb{E} \lbrack x_{i,t} \rbrack \right) - \sum_{t=1}^{H} \mathbb{E} \lbrack x_{S_t,t} \rbrack $.} \\\\
\\rowcolor{white}
\\multicolumn{8}{l}{\footnotesize $^{\star\star}$ Średnia, wyrażona w procentach liczba iteracji, w których algorytm dokonał optymalnego wyboru.} \\\\
\\endlastfoot

';
    for ($i = 0; $limit > 0 && $i < count($standings['regret']); ++$i) {
        $idx = $standings['regret'][$i];
        $result = $results[$standings['regret'][$i]];
        $limit--;
        $latexTable .= ($i % 2 == 0 ? '' : '\rowcolor{lightgray2}') . '
';
        $latexTable .= '\footnotesize $' . ($i + 1) . '$ & \footnotesize ' . strtr($result['policy']['name'], ['=' => '{=}'])
            . ' & \footnotesize ' . ($standings['regret'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['regret']['mean'], 2, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['regret']['st.dev'], 2, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['bestArmPulls'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['bestArmPulls']['mean'] * 100, 2, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['bestArmPulls']['st.dev'] * 100, 2, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['time'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['time']['mean'], 4, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['memory'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['memory']['mean'], 0, '{,}', '') . '$'
            . '\\\\
';
    }
    $latexTable .= '\\end{longtable}
\\end{center}';
    file_put_contents($standingsDir . '/table_long_' . $experimentMd5 . '.tex', $latexTable);
    $allLatexTables .=  $latexTable;
    
    
    $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
    $latexTable = '\\begin{center}
\\begin{table}
\\caption{Uśrednione po ' . $repetitions . ' powtórzeniach wyniki eksperymentu posortowane wg. całkowitej straty dla problemu: $\{$';
    for ($i = 0; $i < count($arms); ++$i) {
        $latexTable .= ($i === 0 ? '' : ', ') . '$\\Bernoulli{' . strtr($arms[$i], ['.' => '{,}']) . '}$';
    }
    $latexTable .= '$\}$ z liczbą ramion $K{=}' . count($arms) . '$ oraz skończoną liczbą iteracji $ H {=} ' . $h . '$.'
        . (count($standings['regret']) > $limit ? ' Tabela przedstawia ' . $limit . ' najlepszych wyników z ' . count($standings['regret']) . '.' : '') . '}
\\label{table:' . $experimentMd5 . '}
\\begin{tabular}{|m{4mm}<{\centering}m{60mm}<{\raggedright}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{12mm}<{\raggedleft}m{18mm}<{\raggedleft}m{18mm}<{\raggedleft}|}
    
\\hline 
\\multirow{2}{*}[-0.1ex]{\footnotesize \\#}
& \\multirow{2}{*}[-0.1ex]{\footnotesize Algorytm strategii wyboru}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Całkowita strata$^\star$}
& \\multicolumn{2}{|m{26mm}<{\centering}}{\footnotesize Optymalny wybór$^{\star\star}$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize Czas wykonania~($s$)}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize Zajęta pamięć~($B$)} \\\\
\\cline{3-8}
&
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $\\bar{x}$} & \\multicolumn{1}{|m{12mm}<{\centering}}{\footnotesize $SD$}
& \\multicolumn{1}{|m{18mm}<{\centering}}{\footnotesize $\\bar{x}$}
& \\multicolumn{1}{|m{18mm}<{\centering}|}{\footnotesize $\\bar{x}$} \\\\
\\hline

';
    for ($i = 0; $limit > 0 && $i < count($standings['regret']); ++$i) {
        $idx = $standings['regret'][$i];
        $result = $results[$standings['regret'][$i]];
        $limit--;
        $latexTable .= ($i % 2 == 0 ? '' : '\rowcolor{lightgray2}') . '
';
        $latexTable .= '\footnotesize $' . ($i + 1) . '$ & \footnotesize ' . strtr($result['policy']['name'], ['=' => '{=}'])
            . ' & \footnotesize ' . ($standings['regret'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['regret']['mean'], 2, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['regret']['st.dev'], 2, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['bestArmPulls'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['bestArmPulls']['mean'] * 100, 2, '{,}', '') . '$'
            . ' & \footnotesize $\pm' . number_format($result['bestArmPulls']['st.dev'] * 100, 2, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['time'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['time']['mean'], 4, '{,}', '') . '$'
            . ' & \footnotesize ' . ($standings['memory'][0] === $idx ? '\boldmath' : '') . '$' . number_format($result['memory']['mean'], 0, '{,}', '') . '$'
            . '\\\\
';
    }
    $latexTable .= '\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{white}\\hline
\\arrayrulecolor{black}
\\rowcolor{white}
\\multicolumn{8}{l}{\footnotesize $^\star$ Średnia całkowita strata, liczoną wg. następującego wzoru: $ \bar{R}^E{=} \sum_{t=1}^{H} \left( \max\limits_{i=1,\ldots, K} \mathbb{E} \lbrack x_{i,t} \rbrack \right) - \sum_{t=1}^{H} \mathbb{E} \lbrack x_{S_t,t} \rbrack $.} \\\\
\\rowcolor{white}
\\multicolumn{8}{l}{\footnotesize $^{\star\star}$ Średnia, wyrażona w procentach liczba iteracji, w których algorytm dokonał optymalnego wyboru.} \\\\
        
\\end{tabular}
\\end{table}
\\end{center}';
    file_put_contents($standingsDir . '/table_' . $experimentMd5 . '.tex', $latexTable);
    //$allLatexTables .=  $latexTable;
    # </editor-fold>
    # <editor-fold defaultstate="collapsed" desc="Gather The Summative Standings">
    foreach (['regret', 'time', 'memory', 'bestArmPulls'] as $metrics) {
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
    'regret' => 'całkowitą stratę, liczoną wg. następującego wzoru: $ \bar{R}^E{=} \sum_{t=1}^{H} \left( \max\limits_{i=1,\ldots, K} \mathbb{E} \lbrack x_{i,t} \rbrack \right) - \sum_{t=1}^{H} \mathbb{E} \lbrack x_{S_t,t} \rbrack $',
    'time' => 'czas wykonania',
    'memory' => 'zajętą pamięć',
    'bestArmPulls' => 'liczbę iteracji, w których algorytm dokonał optymalnego wyboru'
]; 
foreach (['regret', 'time', 'memory', 'bestArmPulls'] as $metrics) {
    foreach (array_keys($experiment['policies']) as $idx) {
        $summativeStandings[$idx][$metrics][] = number_format(array_reduce($summativeStandings[$idx][$metrics], function ($carry, $place) use ($nofProblems, $nofPolicies) {
            return $carry + 1/$nofProblems * (1 - ($place - 1) / ($nofPolicies - 1));
        }, 0), 3, '{,}', '');
        $summativeStandings[$idx][$metrics] = array_map(function ($value) {
            return ($value === 1 ? '\\boldmath' : '') . '$' . $value . '$';
        }, $summativeStandings[$idx][$metrics]);
    }
    uasort($summativeStandings, function ($policy1, $policy2) use ($metrics, $nofProblems) {
        return cmp($policy2[$metrics][$nofProblems], $policy1[$metrics][$nofProblems]);
    });
    $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
    $latexTable = '\\begin{center}\\begin{longtable}{|m{4mm}<{\centering}m{' . (164 - 2 - 6 - 16 - 7 * $nofProblems) . 'mm}<{\raggedright}' . str_repeat('m{5mm}<{\centering}', $nofProblems). 'm{14mm}<{\raggedleft}|}
\\caption{Zestawienie wyników, w którym algorytmy strategii wyboru zostały posortowane wg. wartości ważonej funkcji kompromisu wyliczonej na podstawie 
miejsc w rankingach dla założonych w eksperymencie równoważnych problemów ze względu na ' . $metricsLabels[$metrics] . '.'
    . (count($summativeStandings) > $limit ? ' Tabela przedstawia ' . $limit . ' najlepszych wyników z ' . count($summativeStandings) . '.' : '') . '}
\\label{table:' . $experiment['name'] . '_' . $metrics . '} \\\\
 
\\hline 
\\multicolumn{1}{|m{4mm}<{\centering}|}{\footnotesize \\#}
& \\multicolumn{1}{m{' . (164 - 2 - 6 - 16 - 7 * $nofProblems) . 'mm}<{\raggedright}|}{\footnotesize Algorytm strategii wyboru}';
for ($i = 1; $i <= $nofProblems; ++$i) {
    $latexTable .= ' & \\multicolumn{1}{m{5mm}<{\centering}|}{\footnotesize ' . $i . '$^\star$}';
}
$latexTable .= '& \\multicolumn{1}{m{14mm}<{\centering}|}{\footnotesize $A^{WSM}$$^{\star\star}$} \\\\
\\hline 
\\endfirsthead

\\multicolumn{' . ($nofProblems + 3) . '}{c}{\\footnotesize \\tablename\\ \\thetable{} -- ciąg dalszy z poprzedniej strony} \\\\
\\hline  
\\multicolumn{1}{|m{4mm}<{\centering}|}{\footnotesize \\#}
& \\multicolumn{1}{m{' . (164 - 2 - 6 - 16 - 7 * $nofProblems) . 'mm}<{\raggedright}|}{\footnotesize Algorytm strategii wyboru}';
for ($i = 1; $i <= $nofProblems; ++$i) {
    $latexTable .= ' & \\multicolumn{1}{m{5mm}<{\centering}|}{\footnotesize ' . $i . '$^\star$}';
}
$latexTable .= '& \\multicolumn{1}{m{14mm}<{\centering}|}{\footnotesize $A^{WSM}$$^{\star\star}$} \\\\
\\hline 
\\endhead

\\hline
\\multicolumn{' . ($nofProblems + 3) . '}{|r|}{\\footnotesize kontynuacja na następnej stronie} \\\\
\\hline
\\endfoot

\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\\arrayrulecolor{black}
\\multicolumn{' . ($nofProblems + 3) . '}{l}{\footnotesize $^\\star$ Numer problemu, dane w tabeli oznaczają miejsce w rankingu.} \\\\
\\multicolumn{' . ($nofProblems + 3) . '}{l}{\\footnotesize $^{\\star\\star}$ $A^{WSM}{=}\sum\nolimits_{j=1}^{' . $nofProblems . '} (1{/}' . $nofProblems . ') (1 - (a_{ij} {-} 1) {/} ' . ($nofPolicies - 1) . ')$ gdzie $ a_{ij} $ to miejsce zajęte przez $ i $-tą strategię w $ j $-tym problemie.} \\\\
\\endlastfoot

';
$place = 1;
foreach ($summativeStandings as $policyIdx => $results) {
    $latexTable .= ($place % 2 == 1 ? '' : '\rowcolor{lightgray2}') . '
';
    $latexTable .= '\footnotesize $' . $place++ . '$ & \footnotesize ' . strtr($policies[$policyIdx], ['=' => '{=}']) . ' & \footnotesize ' . implode(' & \footnotesize ', $results[$metrics]) . ' \\\\
';
    $limit--;
    if ($limit === 0) {
        break;
    }
}
    $latexTable .= '\\end{longtable}\end{center}';
    file_put_contents($standingsDir . '/table_long_' . $metrics . '.tex', $latexTable);
    $allLatexTables .= $latexTable;
    
    
    
    $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
    $latexTable = '\\begin{center}\\table
\\caption{Zestawienie wyników, w którym algorytmy strategii wyboru zostały posortowane wg. wartości ważonej funkcji kompromisu wyliczonej na podstawie 
miejsc w rankingach dla założonych w eksperymencie równoważnych problemów ze względu na ' . $metricsLabels[$metrics] . '.'
    . (count($summativeStandings) > $limit ? ' Tabela przedstawia ' . $limit . ' najlepszych wyników z ' . count($summativeStandings) . '.' : '') . '}
\\label{table:' . $experiment['name'] . '_' . $metrics . '}
\\begin{tabular}{|m{4mm}<{\centering}m{' . (164 - 2 - 6 - 16 - 7 * $nofProblems) . 'mm}<{\raggedright}' . str_repeat('m{5mm}<{\centering}', $nofProblems). 'm{14mm}<{\raggedleft}|}
 
\\hline 
\\multicolumn{1}{|m{4mm}<{\centering}|}{\footnotesize \\#}
& \\multicolumn{1}{m{' . (164 - 2 - 6 - 16 - 7 * $nofProblems) . 'mm}<{\raggedright}|}{\footnotesize Algorytm strategii wyboru}';
for ($i = 1; $i <= $nofProblems; ++$i) {
    $latexTable .= ' & \\multicolumn{1}{m{5mm}<{\centering}|}{\footnotesize ' . $i . '$^\star$}';
}
$latexTable .= '& \\multicolumn{1}{m{14mm}<{\centering}|}{\footnotesize $A^{WSM}$$^{\star\star}$} \\\\
\\hline 
';
$place = 1;
foreach ($summativeStandings as $policyIdx => $results) {
    $latexTable .= ($place % 2 == 1 ? '' : '\rowcolor{lightgray2}') . '
';
    $latexTable .= '\footnotesize $' . $place++ . '$ & \footnotesize ' . strtr($policies[$policyIdx], ['=' => '{=}']) . ' & \footnotesize ' . implode(' & \footnotesize ', $results[$metrics]) . ' \\\\
';
    $limit--;
    if ($limit === 0) {
        break;
    }
}
    $latexTable .= '\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\arrayrulecolor{white}\hline
\\arrayrulecolor{black}
\\multicolumn{' . ($nofProblems + 3) . '}{l}{\footnotesize $^\\star$ Numer problemu, dane w tabeli oznaczają miejsce w rankingu.} \\\\
\\multicolumn{' . ($nofProblems + 3) . '}{l}{\\footnotesize $^{\\star\\star}$ $A^{WSM}{=}\sum\nolimits_{j=1}^{' . $nofProblems . '} (1{/}' . $nofProblems . ') (1 - (a_{ij} {-} 1) {/} ' . ($nofPolicies - 1) . ')$ gdzie $ a_{ij} $ to miejsce zajęte przez $ i $-tą strategię w $ j $-tym problemie.} \\\\
\\end{tabular}\\end{table}\end{center}';
    file_put_contents($standingsDir . '/table_' . $metrics . '.tex', $latexTable);
}
# </editor-fold>
$template = file_get_contents($configuration['tables.template.filepath']);
file_put_contents($standingsDir . '/all_tables.tex', strtr($template, ['{{tables}}' => $allLatexTables]));
$out = shell_exec('pdflatex -interaction=nonstopmode -shell-escape ' . realpath($standingsDir . '/all_tables.tex'));
foreach (glob('all_tables*') as $filename) {
    if (strpos($filename, '.pdf') !== false) {
        copy($filename, $standingsDir . '/all_tables.pdf');
    }
    unlink($filename);
}
file_put_contents($standingsDir . '/standings.end', 'done');
echo '[i] The standings have been generated in the path: ' . realpath($standingsDir) . PHP_EOL;
