<?php
// app/controllers/AuthController.php
use App\Database;
use App\Response;
use App\Validators;
use App\Auth;

class AuthController {
  public function register() {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $missing = Validators::requireFields($input, ['username','email','password','user_type']);
    if ($missing) Response::error(422, 'Missing fields', $missing);

    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = :u OR email = :e");
    $stmt->execute([':u'=>$input['username'], ':e'=>$input['email']]);
    if ($stmt->fetch()) Response::error(409, 'Username or email already exists');

    $hash = password_hash($input['password'], PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users (username,email,password_hash,user_type) VALUES (:u,:e,:p,:t)")
        ->execute([':u'=>$input['username'], ':e'=>$input['email'], ':p'=>$hash, ':t'=>$input['user_type']]);

    $user_id = $pdo->lastInsertId();
    Response::ok(['user_id' => (int)$user_id], 'Registration successful');
  }

  public function login() {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $missing = App\Validators::requireFields($input, ['username','password']);
    if ($missing) App\Response::error(422, 'Missing fields', $missing);

    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT user_id,username,password_hash,user_type,is_active FROM users WHERE username=:u");
    $stmt->execute([':u'=>$input['username']]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_active'] || !password_verify($input['password'], $user['password_hash'])) {
      App\Response::error(401, 'Invalid credentials');
    }
    App\Auth::login($user);
    App\Response::ok(['user' => ['user_id'=>$user['user_id'], 'username'=>$user['username'], 'user_type'=>$user['user_type']]], 'Login successful');
  }

  public function logout() { App\Auth::logout(); }
}
?>