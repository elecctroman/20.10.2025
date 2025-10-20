<?php
namespace App\Core;

use PDO;

abstract class Model
{
    protected static ?PDO $db = null;

    public function __construct()
    {
        if (static::$db === null) {
            static::$db = database();
        }
    }

    protected function query(string $sql, array $params = [])
    {
        $stmt = static::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
