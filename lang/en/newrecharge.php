<?php

return [
    'history' => [
        'status' => [
            0 => 'Processing..',
            1 => 'Unpaid.',
            2 => 'Payment failure.',
            3 => 'Payment success.',
            4 => 'Payment success (have changes)',
            5 => 'Payment not enough. Money is refuned.'
        ],
        'game_status' => [
            0 => 'Processing..',
            1 => 'Success',
            2 => 'Retrying..'
        ],
        'empty' => 'You have no payments!'
    ],
    'not-enough-money' => 'Payment not enough money (money has been returned to web ewallet)',
    'shop-error' => 'Loading shop error! Please try again!',
    'recharge-error' => 'Payment error! Please try again!',
    'shop-empty' => 'Shop is empty!',
    'callback-error' => 'Payment error. Contact customer support (callback-error)',
    'query-error' => 'Payment querying error, please try again!',
    'pending' => 'Payment need more actions and/or more time to complete!',
    'success' => 'Payment success!',
    'callback-in-progress' => 'Processing.. please kindly wait..',
];