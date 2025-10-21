<?php
namespace App\Services\Gateways;

class Mock
{
    public function __construct(array $options = [])
    {
    }

    public function charge(array $payload): array
    {
        return [
            'success' => true,
            'transaction_id' => uniqid('mock_', true),
            'message' => 'Ödeme başarıyla simüle edildi.',
        ];
    }
}
