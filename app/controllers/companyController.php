<?php
// app/controllers/CompanyController.php
use App\Database;
use App\Response;

class CompanyController {
  public function index() {
    $pdo = App\Database::conn();
    $rows = $pdo->query("SELECT * FROM companies ORDER BY company_name")->fetchAll();
    App\Response::ok($rows);
  }
  public function show($params) {
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE company_id=:id");
    $stmt->execute([':id'=>$params['company_id']]);
    $row = $stmt->fetch();
    if (!$row) App\Response::error(404, 'Company not found');
    App\Response::ok($row);
  }
  public function store() {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $pdo = App\Database::conn();
    $stmt = $pdo->prepare("INSERT INTO companies (company_name,address,supervisor_name,contact_number,email)
                           VALUES (:n,:a,:s,:c,:e)");
    $stmt->execute([':n'=>$input['company_name'], ':a'=>$input['address']??null, ':s'=>$input['supervisor_name']??null, ':c'=>$input['contact_number']??null, ':e'=>$input['email']??null]);
    App\Response::ok(['company_id' => (int)$pdo->lastInsertId()], 'Company created');
  }
  public function update($params) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $fields = ['company_name','address','supervisor_name','contact_number','email'];
    $set=[]; $bind=[':id'=>$params['company_id']];
    foreach ($fields as $f) if (isset($input[$f])) { $set[]="$f=:$f"; $bind[":$f"]=$input[$f]; }
    if (!$set) App\Response::error(422, 'No fields to update');
    $pdo = App\Database::conn();
    $pdo->prepare("UPDATE companies SET ".implode(', ',$set)." WHERE company_id=:id")->execute($bind);
    App\Response::ok([], 'Company updated');
  }
}
