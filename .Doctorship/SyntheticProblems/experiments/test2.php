<?php
return [
    'repetitions' => 10,
    'arms' => [
        [0.5, 0],
    ],
    'policies' => [
        ['archtype' => 'TakeFixedArm', 'params' => ['armIndex' => 0]],
        ['archtype' => 'TakeFixedArm', 'params' => ['armIndex' => 1]]
    ]
];
