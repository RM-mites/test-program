<?php
// app/Config.php
namespace App;

class Config {
  public static function db(): array {
    return [
      'host' => '127.0.0.1',
      'dbname' => 'ojt_monitoring_system',
      'user' => 'root',
      'pass' => '',
      'charset' => 'utf8mb4'
    ];
  }

  // Toggle: 'session' or 'jwt'
  public const AUTH_METHOD = 'session';
  public const JWT_SECRET = 'change_this_in_env';
  public const TOKEN_EXP_SECONDS = 3600;
}
?>