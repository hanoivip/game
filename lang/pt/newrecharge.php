<?php

return [
    'history' => [
        'status' => [
            0 => 'Processando..',
            1 => 'Não pago.',
            2 => 'Falha no pagamento.',
            3 => 'Pagamento bem-sucedido.',
            4 => 'Pagamento com sucesso (possuem alterações)',
            5 => 'O dinheiro não é suficiente e é reembolsado para webcoins.'
        ],
        'game_status' => [
            0 => 'Processando..',
            1 => 'Sucesso',
            2 => 'Tentando novamente..'
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