<?php
namespace App\Core;

class Mailer
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('mail');
    }

    public function send(string $to, string $subject, string $view, array $data = []): bool
    {
        $body = view($view, $data, 'email', true);

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $this->config['from']['name'] . ' <' . $this->config['from']['address'] . '>';

        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
    }
}
