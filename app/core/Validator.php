<?php
namespace App\Core;

class Validator
{
    public static function make(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = trim($data[$field] ?? '');
            foreach (explode('|', $rule) as $segment) {
                if ($segment === 'required' && $value === '') {
                    $errors[$field][] = 'Bu alan zorunludur.';
                }

                if ($segment === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Geçerli bir e-posta adresi giriniz.';
                }

                if ($segment === 'accepted' && !in_array($value, ['1', 'on', 'true'], true)) {
                    $errors[$field][] = 'Devam etmek için kabul etmeniz gerekir.';
                }

                if (str_starts_with($segment, 'min:')) {
                    $min = (int) substr($segment, 4);
                    if (mb_strlen($value) < $min) {
                        $errors[$field][] = "En az {$min} karakter olmalıdır.";
                    }
                }

                if (str_starts_with($segment, 'max:')) {
                    $max = (int) substr($segment, 4);
                    if (mb_strlen($value) > $max) {
                        $errors[$field][] = "En fazla {$max} karakter olmalıdır.";
                    }
                }
            }
        }

        return $errors;
    }
}
