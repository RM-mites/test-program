<?php
// public/index.php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/database.php';
require_once __DIR__ . '/../app/response.php';
require_once __DIR__ . '/../app/router.php';
require_once __DIR__ . '/../app/authentication.php';
require_once __DIR__ . '/../app/middleware.php';
require_once __DIR__ . '/../app/validators.php';

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
$router->post('/api/auth/register', ['authenticationControl', 'register']);
$router->post('/api/auth/login', ['authenticationControl', 'login']);
$router->post('/api/auth/logout', ['authenticationControl', 'logout']);

// Students
$router->get('/api/students/:student_id', ['studController', 'show'], [Middleware::auth()]);
$router->put('/api/students/:student_id', ['studController', 'update'], [Middleware::auth()]);
$router->get('/api/students/:student_id/company', ['studController', 'company'], [Middleware::auth()]);

// Companies
$router->get('/api/companies', ['companyController', 'index'], [Middleware::auth()]);
$router->get('/api/companies/:company_id', 'companyController', 'show'], [Middleware::auth()]);
$router->post('/api/companies', 'companyController', 'store'], [Middleware::auth(['admin'])]);
$router->put('/api/companies/:company_id', 'companyController', 'update'], [Middleware::auth(['admin'])]);

// DTR
$router->post('/api/dtr/clock-in', ['DTRController', 'clockIn'], [Middleware::auth(['student'])]);
$router->post('/api/dtr/clock-out', ['DTRController', 'clockOut'], [Middleware::auth(['student'])]);
$router->get('/api/dtr/:student_id', ['DTRController', 'list'], [Middleware::auth()]);
$router->get('/api/dtr/:student_id/summary', ['DTRController', 'summary'], [Middleware::auth()]);

// Activities
$router->post('/api/activities', ['ActController', 'store'], [Middleware::auth(['student'])]);
$router->get('/api/activities/:student_id', ['ActController', 'list'], [Middleware::auth()]);
$router->put('/api/activities/:activity_id', ['ActController', 'update'], [Middleware::auth()]);
$router->get('/api/activities/:activity_id/summary', ['ActController', 'summary'], [Middleware::auth()]);

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