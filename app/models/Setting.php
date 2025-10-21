<?php
namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    public function all(): array
    {
        $stmt = $this->query('SELECT * FROM settings');
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }
}
