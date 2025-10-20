<?php
namespace App\Services\Gateways;

class Iyzico
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function charge(array $payload): array
    {
        return [
            'success' => true,
            'transaction_id' => uniqid('iyzico_', true),
            'message' => 'Iyzico ödemesi kabul edildi.',
        ];
    }
}
