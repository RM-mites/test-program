<?php
// app/Database.php
namespace App;
use PDO, PDOException;

class Database {
  private static ?PDO $pdo = null;

  public static function conn(): PDO {
    if (self::$pdo) return self::$pdo;
    $c = Config::db();
    $dsn = "mysql:host={$c['host']};dbname={$c['dbname']};charset={$c['charset']}";
    self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return self::$pdo;
  }
}
?>