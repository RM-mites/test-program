<?php
// app/Response.php
namespace App;

class Response {
  public static function ok($data = [], string $message = 'Operation successful'): void {
    self::json(200, ['success' => true, 'message' => $message, 'data' => $data]);
  }
  public static function error(int $code, string $message, $details = []): void {
    self::json($code, ['success' => false, 'message' => $message, 'error_code' => $code, 'details' => $details]);
  }
  private static function json(int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
  }
}
?>