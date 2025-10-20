<?php
namespace App\Services;

class PaymentGateway
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('payment');
    }

    public function charge(string $method, array $payload): array
    {
        $gatewayConfig = $this->config['gateways'][$method] ?? null;

        if (!$gatewayConfig) {
            return ['success' => false, 'message' => 'Ödeme yöntemi desteklenmiyor.'];
        }

        $class = $gatewayConfig['class'];
        $gateway = new $class($gatewayConfig['options']);

        return $gateway->charge($payload);
    }
}
