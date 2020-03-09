<?php
return [
    'repetitions' => 100,
    'arms' => [
        // Auer
        [0.9, 0.6],
        [0.9, 0.8],
        [0.55, 0.45]
    ],
    'policies' => [
        /**
         * Syntax:
         * ['archtype' => '{a class name}', 'params' => [ ... ]]
         * The parameters are very direct generating method:
         * ['z'=>'1.97'] produces {"z": 1.97}
         * ['z'=>'test'] produces {"z": test}
         * ['z'=>'str:test'] produces {"z": "test"}
         */
        ['archtype' => 'TSPolP', 'params' => ['alpha' => 0.1, 'z' => 2.33]],
        ['archtype' => 'UCB', 'params' => []],
    ]
];
