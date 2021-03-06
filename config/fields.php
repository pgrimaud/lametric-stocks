<?php

use LaMetric\Field;

return [
    [
        'key'  => 'symbol',
        'type' => Field::TEXT_TYPE,
    ],
    [
        'key'  => 'stock_name',
        'type' => Field::SWITCH_TYPE,
    ],
    [
        'key'  => 'daily_change',
        'type' => Field::SWITCH_TYPE,
    ],
];
