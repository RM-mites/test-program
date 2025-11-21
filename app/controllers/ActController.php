<?php
// app/controllers/ActivityController.php
use App\Database;
use App\Response;

class ActivityController {
  public function store() {
    $user = $GLOBALS['auth_user'];
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $pdo = App\Database::conn();

    $sid = $pdo->prepare("SELECT student_id FROM students WHERE user_id=:uid");
    $sid->execute([':uid'=>$user['user_id']]);
    $student = $sid->fetch();
    if (!$student) App\Response::error(404, 'Student profile not found');

    $stmt = $pdo->prepare("INSERT INTO activity_logs (student_id, week_starting, week_ending, task_description, hours_rendered, accomplishments, status)
                           VALUES (:sid, :ws, :we, :td, :hr, :ac, :st)");
    $stmt->execute([
      ':sid'=>$student['student_id'],
      ':ws'=>$input['week_starting'],
      ':we'=>$input['week_ending'],
      ':td'=>$input['task_description'] ?? '',
      ':hr'=>$input['hours_rendered'] ?? 0,
      ':ac'=>$input['accomplishments'] ?? '',
      ':st'=>$input['status'] ?? 'draft'
    ]);
    App\Response::ok(['activity_id' => (int)$pdo->lastInsertId()], 'Activity log created');
  }

  public function list($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE student_id=:sid ORDER BY week_starting DESC");
    $stmt->execute([':sid'=>$params['student_id']]);
    App\Response::ok($stmt->fetchAll());
  }

  public function update($params) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $fields = ['task_description','hours_rendered','accomplishments','status'];
    $set=[]; $bind=[':id'=>$params['activity_id']];
    foreach ($fields as $f) if (isset($input[$f])) { $set[]="$f=:$f"; $bind[":$f"]=$input[$f]; }
    if (!$set) App\Response::error(422, 'No fields to update');
    $pdo = App\Database::conn();
    $pdo->prepare("UPDATE activity_logs SET ".implode(', ',$set)." WHERE activity_id=:id")->execute($bind);
    App\Response::ok([], 'Activity updated');
  }

  public function summary($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT hours_rendered FROM activity_logs WHERE activity_id=:id");
    $stmt->execute([':id'=>$params['activity_id']]);
    $row = $stmt->fetch();
    if (!$row) App\Response::error(404, 'Activity not found');
    App\Response::ok(['hours_rendered'=>$row['hours_rendered']]);
  }
}
?>