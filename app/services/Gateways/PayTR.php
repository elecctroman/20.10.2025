<?php
namespace App\Services\Gateways;

class PayTR
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
            'transaction_id' => uniqid('paytr_', true),
            'message' => 'PayTR ödemesi kabul edildi.',
        ];
    }
}
