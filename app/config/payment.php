<?php
return [
    'default' => 'mock',
    'gateways' => [
        'mock' => [
            'class' => \App\Services\Gateways\Mock::class,
            'options' => [],
        ],
        'paytr' => [
            'class' => \App\Services\Gateways\PayTR::class,
            'options' => [
                'merchant_id' => '',
                'merchant_key' => '',
                'merchant_salt' => '',
            ],
        ],
        'iyzico' => [
            'class' => \App\Services\Gateways\Iyzico::class,
            'options' => [
                'api_key' => '',
                'secret_key' => '',
            ],
        ],
    ],
];
