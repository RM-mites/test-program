<?php
// public/index.php
require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Response.php';
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Middleware.php';
require_once __DIR__ . '/../app/Validators.php';

spl_autoload_register(function($class) {
  $base = __DIR__ . '/../app/';
  $paths = ["controllers/$class.php", "models/$class.php"];
  foreach ($paths as $path) {
    $file = $base . $path;
    if (file_exists($file)) { require_once $file; return; }
  }
});

use App\Router;
use App\Middleware;

$router = new Router();

// Auth
$router->post('/api/auth/register', ['AuthController', 'register']);
$router->post('/api/auth/login', ['AuthController', 'login']);
$router->post('/api/auth/logout', ['AuthController', 'logout']);

// Students
$router->get('/api/students/:student_id', ['StudentController', 'show'], [Middleware::auth()]);
$router->put('/api/students/:student_id', ['StudentController', 'update'], [Middleware::auth()]);
$router->get('/api/students/:student_id/company', ['StudentController', 'company'], [Middleware::auth()]);

// Companies
$router->get('/api/companies', ['CompanyController', 'index'], [Middleware::auth()]);
$router->get('/api/companies/:company_id', ['CompanyController', 'show'], [Middleware::auth()]);
$router->post('/api/companies', ['CompanyController', 'store'], [Middleware::auth(['admin'])]);
$router->put('/api/companies/:company_id', ['CompanyController', 'update'], [Middleware::auth(['admin'])]);

// DTR
$router->post('/api/dtr/clock-in', ['DtrController', 'clockIn'], [Middleware::auth(['student'])]);
$router->post('/api/dtr/clock-out', ['DtrController', 'clockOut'], [Middleware::auth(['student'])]);
$router->get('/api/dtr/:student_id', ['DtrController', 'list'], [Middleware::auth()]);
$router->get('/api/dtr/:student_id/summary', ['DtrController', 'summary'], [Middleware::auth()]);

// Activities
$router->post('/api/activities', ['ActivityController', 'store'], [Middleware::auth(['student'])]);
$router->get('/api/activities/:student_id', ['ActivityController', 'list'], [Middleware::auth()]);
$router->put('/api/activities/:activity_id', ['ActivityController', 'update'], [Middleware::auth()]);
$router->get('/api/activities/:activity_id/summary', ['ActivityController', 'summary'], [Middleware::auth()]);

// Announcements
$router->post('/api/announcements', ['AnnouncementController', 'store'], [Middleware::auth(['admin','coordinator'])]);
$router->get('/api/announcements', ['AnnouncementController', 'index'], [Middleware::auth()]);
$router->get('/api/announcements/:announcement_id', ['AnnouncementController', 'show'], [Middleware::auth()]);
$router->put('/api/announcements/:announcement_id', ['AnnouncementController', 'update'], [Middleware::auth(['admin','coordinator'])]);
$router->delete('/api/announcements/:announcement_id', ['AnnouncementController', 'destroy'], [Middleware::auth(['admin','coordinator'])]);

// Admin dashboard
$router->get('/api/admin/students', ['AdminController', 'students'], [Middleware::auth(['admin','coordinator'])]);
$router->get('/api/admin/students/:student_id/hours', ['AdminController', 'hours'], [Middleware::auth(['admin','coordinator'])]);
$router->get('/api/admin/students/:student_id/activities', ['AdminController', 'activities'], [Middleware::auth(['admin','coordinator'])]);
$router->get('/api/admin/dashboard/summary', ['AdminController', 'summary'], [Middleware::auth(['admin','coordinator'])]);

$router->run();
?>