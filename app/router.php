<?php
// app/Router.php
namespace App;

class Router {
  private array $routes = [];

  public function get($path, $handler, $middlewares = []) { $this->map('GET', $path, $handler, $middlewares); }
  public function post($path, $handler, $middlewares = []) { $this->map('POST', $path, $handler, $middlewares); }
  public function put($path, $handler, $middlewares = []) { $this->map('PUT', $path, $handler, $middlewares); }
  public function delete($path, $handler, $middlewares = []) { $this->map('DELETE', $path, $handler, $middlewares); }

  private function map($method, $path, $handler, $middlewares) {
    $this->routes[] = compact('method','path','handler','middlewares');
  }

  public function run() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    foreach ($this->routes as $route) {
      $pattern = "@^" . preg_replace('@:([a-zA-Z_]+)@', '(?P<\1>[^/]+)', $route['path']) . "$@";
      if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        foreach ($route['middlewares'] as $mw) { $mw($params); }
        [$class, $methodName] = $route['handler'];
        $controller = new $class();
        return $controller->$methodName($params);
      }
    }
    Response::error(404, 'Not Found');
  }
}
?>