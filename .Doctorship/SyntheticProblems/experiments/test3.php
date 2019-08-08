<?php
return [
    'repetitions' => 10,
    'arms' => [
        // Auer
        [0.9, 0.6],
        [0.9, 0.8],
        [0.55, 0.45],
        [0.9, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6, 0.6],
        [0.9, 0.8, 0.8, 0.8, 0.7, 0.7, 0.7, 0.6, 0.6, 0.6],
        [0.9, 0.8, 0.8, 0.8, 0.8, 0.8, 0.8, 0.8, 0.8, 0.8],
        [0.55, 0.45, 0.45, 0.45, 0.45, 0.45, 0.45, 0.45, 0.45, 0.45],
        // Audibert
        array_merge([0.5], array_fill(0, 19, 0.4)),
        array_merge([0.5], array_fill(0, 5, 0.42), array_fill(0, 14, 0.38)),
        array_merge([0.5], array_map(function ($val) {
            return 0.5 - pow(0.37, $val);
        }, range(2, 4))),
        [0.5, 0.42, 0.4, 0.4, 0.35, 0.35],
        array_merge([0.5], array_map(function ($val) {
            return 0.5 - 0.025 * $val;
        }, range(2, 15))),
        array_merge([0.5, 0.48], array_fill(0, 18, 0.37)),
        array_merge([0.5], array_fill(0, 5, 0.45), array_fill(0, 14, 0.43), array_fill(0, 10, 0.38)),
    ],
    'policies' => [
        ['archtype' => 'TakeFixedArm', 'params' => ['armIndex' => 0]],
        ['archtype' => 'TakeFixedArm', 'params' => ['armIndex' => 1]]
    ]
];
