<?php
return [
    'repetitions' => 100,
    'arms' => [
        [0.9, 0.6],
        array_merge([0.5, 0.48], array_fill(0, 18, 0.37))
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
