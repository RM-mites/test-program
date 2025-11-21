<?php
// app/Validators.php
namespace App;

class Validators {
  public static function requireFields(array $data, array $fields): array {
    $missing = [];
    foreach ($fields as $f) if (!isset($data[$f]) || $data[$f] === '') $missing[] = $f;
    return $missing;
  }
}
?>