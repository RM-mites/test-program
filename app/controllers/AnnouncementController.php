<?php
// app/controllers/AnnouncementController.php
use App\Database;
use App\Response;

class AnnouncementController {
  public function store() {
    $user = $GLOBALS['auth_user'];
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("INSERT INTO announcements (admin_id,title,content,announcement_type,scheduled_date,is_active)
                           VALUES (:aid,:t,:c,:type,:sd,:act)");
    $stmt->execute([
      ':aid'=>$user['user_id'],
      ':t'=>$input['title'],
      ':c'=>$input['content'],
      ':type'=>$input['announcement_type'] ?? 'general',
      ':sd'=>$input['scheduled_date'] ?? null,
      ':act'=>$input['is_active'] ?? 1
    ]);
    App\Response::ok(['announcement_id' => (int)$pdo->lastInsertId()], 'Announcement created');
  }

  public function index() {
    $pdo = App\Database::conn();
    $rows = $pdo->query("SELECT * FROM announcements WHERE is_active=1 ORDER BY posted_at DESC")->fetchAll();
    App\Response::ok($rows);
  }

  public function show($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE announcement_id=:id");
    $stmt->execute([':id'=>$params['announcement_id']]);
    $row = $stmt->fetch();
    if (!$row) App\Response::error(404, 'Announcement not found');
    App\Response::ok($row);
  }

  public function update($params) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $fields = ['title','content','announcement_type','scheduled_date','is_active'];
    $set=[]; $bind=[':id'=>$params['announcement_id']];
    foreach ($fields as $f) if (isset($input[$f])) { $set[]="$f=:$f"; $bind[":$f"]=$input[$f]; }
    if (!$set) App\Response::error(422, 'No fields to update');
    $pdo = App\Database::conn();
    $pdo->prepare("UPDATE announcements SET ".implode(', ',$set)." WHERE announcement_id=:id")->execute($bind);
    App\Response::ok([], 'Announcement updated');
  }

  public function destroy($params) {
    $pdo = App\Database::conn();
    $pdo->prepare("DELETE FROM announcements WHERE announcement_id=:id")->execute([':id'=>$params['announcement_id']]);
    App\Response::ok([], 'Announcement deleted');
  }
}
