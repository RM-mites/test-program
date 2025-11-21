<?php
// app/Auth.php
namespace App;
use App\Database;

class Auth {
  public static function login(array $user): void {
    if (Config::AUTH_METHOD === 'session') {
      session_start();
      $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'user_type' => $user['user_type']
      ];
    } else {
      $payload = [
        'sub' => $user['user_id'],
        'username' => $user['username'],
        'user_type' => $user['user_type'],
        'exp' => time() + Config::TOKEN_EXP_SECONDS
      ];
      $token = JWT::encode($payload, Config::JWT_SECRET, 'HS256');
      Response::ok(['token' => $token], 'Logged in');
    }
  }

  public static function currentUser(): ?array {
    if (Config::AUTH_METHOD === 'session') {
      session_start();
      return $_SESSION['user'] ?? null;
    }
    // For JWT, parse Authorization header "Bearer ..."
    $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/', $hdr, $m)) {
      try {
        $decoded = \Firebase\JWT\JWT::decode($m[1], new \Firebase\JWT\Key(Config::JWT_SECRET, 'HS256'));
        return ['user_id' => $decoded->sub, 'username' => $decoded->username, 'user_type' => $decoded->user_type];
      } catch (\Exception $e) { return null; }
    }
    return null;
  }

  public static function logout(): void {
    if (Config::AUTH_METHOD === 'session') {
      session_start();
      session_destroy();
      Response::ok([], 'Logged out');
    } else {
      Response::ok([], 'Token invalidated (client-side)');
    }
  }
}
?>