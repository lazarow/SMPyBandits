<?php
$policies = [];
foreach ([0.1, 0.05, 0.03, 0.01, 0.001] as $alpha) {
    foreach ([2.58, 2.33, 1.96, 1.64, 1.28] as $z) {
        foreach (range(0.1, 4, 0.1) as $beta) {
            $policies[] = ['archtype' => 'TSPolP', 'params' => ['alpha' => $alpha, 'beta' => $beta, 'z' => $z]];
        }
    }
}

return [
    'repetitions' => 100,
    'arms' => [
        [0.9, 0.8, 0.8, 0.8, 0.7, 0.7, 0.7, 0.6, 0.6, 0.6],
        [0.55, 0.45],
        [0.9, 0.6],
        [0.9, 0.8],
        [0.55, 0.45],
        [0.9, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6]
    ],
    'policies' => $policies
];
