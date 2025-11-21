<?php
// app/Middleware.php
namespace App;
use App\Response;

class Middleware {
  public static function auth(array $roles = null): callable {
    return function() use ($roles) {
      $user = Auth::currentUser();
      if (!$user) Response::error(401, 'Unauthorized');
      if ($roles && !in_array($user['user_type'], $roles)) {
        Response::error(403, 'Forbidden');
      }
      // attach user to global for controllers
      $GLOBALS['auth_user'] = $user;
    };
  }
}
?>