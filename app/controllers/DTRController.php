<?php
// app/controllers/DtrController.php
use App\Database;
use App\Response;

class DtrController {
  public function clockIn() {
    $user = $GLOBALS['auth_user'];
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $date = $input['record_date'] ?? date('Y-m-d');
    $pdo = App\Database::conn();

    // find student_id for current user
    $sid = $pdo->prepare("SELECT student_id FROM students WHERE user_id=:uid");
    $sid->execute([':uid'=>$user['user_id']]);
    $student = $sid->fetch();
    if (!$student) App\Response::error(404, 'Student profile not found');

    // insert or update DTR
    $stmt = $pdo->prepare("INSERT INTO daily_time_records (student_id, record_date, time_in, status)
                           VALUES (:sid, :date, :in, 'present')
                           ON DUPLICATE KEY UPDATE time_in = VALUES(time_in), status='present'");
    $stmt->execute([':sid'=>$student['student_id'], ':date'=>$date, ':in'=>date('H:i:s')]);
    App\Response::ok([], 'Clock-in recorded');
  }

  public function clockOut() {
    $user = $GLOBALS['auth_user'];
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $date = $input['record_date'] ?? date('Y-m-d');
    $pdo = App\Database::conn();

    $sid = $pdo->prepare("SELECT student_id FROM students WHERE user_id=:uid");
    $sid->execute([':uid'=>$user['user_id']]);
    $student = $sid->fetch();
    if (!$student) App\Response::error(404, 'Student profile not found');

    $stmt = $pdo->prepare("SELECT time_in FROM daily_time_records WHERE student_id=:sid AND record_date=:date");
    $stmt->execute([':sid'=>$student['student_id'], ':date'=>$date]);
    $row = $stmt->fetch();
    if (!$row || !$row['time_in']) App\Response::error(409, 'Clock-in missing');

    $timeIn = strtotime($row['time_in']);
    $timeOut = time();
    $hours = round(($timeOut - $timeIn) / 3600, 2);

    $upd = $pdo->prepare("UPDATE daily_time_records SET time_out=:out, daily_hours=:h WHERE student_id=:sid AND record_date=:date");
    $upd->execute([':out'=>date('H:i:s',$timeOut), ':h'=>$hours, ':sid'=>$student['student_id'], ':date'=>$date]);

    // update hours_summary
    $sum = $pdo->prepare("INSERT INTO hours_summary (student_id, total_hours)
                          VALUES (:sid, :h)
                          ON DUPLICATE KEY UPDATE total_hours = total_hours + VALUES(total_hours)");
    $sum->execute([':sid'=>$student['student_id'], ':h'=>$hours]);

    App\Response::ok(['daily_hours'=>$hours], 'Clock-out recorded');
  }

  public function list($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM daily_time_records WHERE student_id=:sid ORDER BY record_date DESC");
    $stmt->execute([':sid'=>$params['student_id']]);
    App\Response::ok($stmt->fetchAll());
  }

  public function summary($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(daily_hours),0) AS total_hours FROM daily_time_records WHERE student_id=:sid");
    $stmt->execute([':sid'=>$params['student_id']]);
    $sum = $stmt->fetch();
    App\Response::ok($sum);
  }
}
?>