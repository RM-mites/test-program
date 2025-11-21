<?php
// app/controllers/StudentController.php
use App\Database;
use App\Response;

class StudentController {
  public function show($params) {
    $pdo = App\Database::conn();
    $sql = "SELECT s.*, c.company_name FROM students s
            LEFT JOIN companies c ON s.company_id = c.company_id
            WHERE s.student_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id'=>$params['student_id']]);
    $row = $stmt->fetch();
    if (!$row) App\Response::error(404, 'Student not found');
    App\Response::ok($row);
  }

  public function update($params) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $fields = ['first_name','last_name','middle_name','course','year_level','contact_number','email_address','address','company_id'];
    $set = []; $bind = [':id'=>$params['student_id']];
    foreach ($fields as $f) if (isset($input[$f])) { $set[] = "$f = :$f"; $bind[":$f"] = $input[$f]; }
    if (!$set) App\Response::error(422, 'No fields to update');
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("UPDATE students SET ".implode(', ', $set)." WHERE student_id=:id");
    $stmt->execute($bind);
    App\Response::ok([], 'Student updated');
  }

  public function company($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT c.* FROM students s INNER JOIN companies c ON s.company_id=c.company_id WHERE s.student_id=:id");
    $stmt->execute([':id'=>$params['student_id']]);
    $row = $stmt->fetch();
    if (!$row) App\Response::error(404, 'Company not assigned');
    App\Response::ok($row);
  }
}
?>