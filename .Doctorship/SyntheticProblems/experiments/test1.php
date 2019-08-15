<?php
return [
    'repetitions' => 100,
    'arms' => [
        [0.9, 0.8, 0.8, 0.8, 0.7, 0.7, 0.7, 0.6, 0.6, 0.6],
        [0.55, 0.45],
        [0.9, 0.6],
        [0.9, 0.8],
        [0.9, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6]
    ],
    'policies' => [
        ['archtype' => 'TSPolP', 'params' => ['alpha' => 0.1, 'z' => 2.33]],
        ['archtype' => 'UCB', 'params' => []],
        ['archtype' => 'AdBandits', 'params' => ['alpha' => '0.5', 'horizon' => 'HORIZON']],
    ]
];
