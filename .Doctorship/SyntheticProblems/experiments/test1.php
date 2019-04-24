<?php
return [
    'repetitions' => 100,
    'arms' => [
        [0.9, 0.8, 0.8, 0.8, 0.7, 0.7, 0.7, 0.6, 0.6, 0.6],
        [0.55, 0.45]
    ],
    'policies' => [
        ['archtype' => 'TSPolP', 'params' => []],
        ['archtype' => 'UCB', 'params' => []],
    ]
];
