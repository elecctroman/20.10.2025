<?php
namespace App\Core;

class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'store')
    {
        return view($template, $data, $layout);
    }

    protected function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }
}
