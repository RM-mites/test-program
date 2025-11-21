<?php
// app/controllers/AdminController.php
use App\Database;
use App\Response;

class AdminController {
  public function students() {
    $pdo = App\Database::conn();
    $params = $_GET;
    $where = []; $bind = [];
    if (!empty($params['company_id'])) { $where[] = 's.company_id=:cid'; $bind[':cid']=$params['company_id']; }
    if (!empty($params['course'])) { $where[] = 's.course=:course'; $bind[':course']=$params['course']; }
    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.course, s.year_level, c.company_name
            FROM students s LEFT JOIN companies c ON s.company_id=c.company_id";
    if ($where) $sql .= " WHERE ".implode(' AND ', $where);
    $sql .= " ORDER BY s.last_name";
    $stmt = $pdo->prepare($sql); $stmt->execute($bind);
    App\Response::ok($stmt->fetchAll());
  }

  public function hours($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(daily_hours),0) AS total_hours FROM daily_time_records WHERE student_id=:sid");
    $stmt->execute([':sid'=>$params['student_id']]);
    App\Response::ok($stmt->fetch());
  }

  public function activities($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE student_id=:sid ORDER BY week_starting DESC");
    $stmt->execute([':sid'=>$params['student_id']]);
    App\Response::ok($stmt->fetchAll());
  }

  public function summary() {
    $pdo = App\Database::conn();
    $stats = [];
    $stats['students'] = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $stats['companies'] = (int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
    $stats['total_hours'] = (float)$pdo->query("SELECT COALESCE(SUM(daily_hours),0) FROM daily_time_records")->fetchColumn();
    $stats['activities_submitted'] = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE status='submitted'")->fetchColumn();
    $stats['activities_approved'] = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE status='approved'")->fetchColumn();
    App\Response::ok($stats);
  }
}
