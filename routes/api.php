cat > routes/api.php << 'EOF'
<?php
declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'];
$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if ($uri === '/' || $uri === '/api') {
    http_response_code(200);
    echo json_encode(['api' => 'TodoCamisetas API', 'version' => '1.0', 'status' => 'OK']);
    exit;
}

function match_route(string $pattern, string $uri, string $method, string $expected): bool|array
{
    if (strtoupper($method) !== strtoupper($expected)) return false;
    if (!preg_match($pattern, $uri, $m)) return false;
    array_shift($m);
    return $m;
}

// CAMISETAS
if ($m = match_route('#^/api/camisetas$#', $uri, $method, 'GET'))    CamisetaController::index();
if ($m = match_route('#^/api/camisetas$#', $uri, $method, 'POST'))   CamisetaController::store();
if ($m = match_route('#^/api/camisetas/(\d+)$#', $uri, $method, 'GET'))    CamisetaController::show((int)$m[0]);
if ($m = match_route('#^/api/camisetas/(\d+)$#', $uri, $method, 'PUT'))    CamisetaController::update((int)$m[0]);
if ($m = match_route('#^/api/camisetas/(\d+)$#', $uri, $method, 'PATCH'))  CamisetaController::update((int)$m[0]);
if ($m = match_route('#^/api/camisetas/(\d+)$#', $uri, $method, 'DELETE')) CamisetaController::destroy((int)$m[0]);
if ($m = match_route('#^/api/camisetas/(\d+)/precio$#', $uri, $method, 'GET'))  CamisetaController::precioFinal((int)$m[0]);
if ($m = match_route('#^/api/camisetas/(\d+)/tallas$#', $uri, $method, 'POST')) TallaController::asociarACamiseta((int)$m[0]);

// CLIENTES
if ($m = match_route('#^/api/clientes$#', $uri, $method, 'GET'))    ClienteController::index();
if ($m = match_route('#^/api/clientes$#', $uri, $method, 'POST'))   ClienteController::store();
if ($m = match_route('#^/api/clientes/(\d+)$#', $uri, $method, 'GET'))    ClienteController::show((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)$#', $uri, $method, 'PUT'))    ClienteController::update((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)$#', $uri, $method, 'PATCH'))  ClienteController::patch((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)$#', $uri, $method, 'DELETE')) ClienteController::destroy((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)/camisetas$#', $uri, $method, 'GET'))  CamisetaController::porCliente((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)/camisetas$#', $uri, $method, 'POST')) CamisetaController::asociarCliente((int)$m[0]);
if ($m = match_route('#^/api/clientes/(\d+)/camisetas/(\d+)$#', $uri, $method, 'DELETE')) CamisetaController::desasociarCliente((int)$m[0], (int)$m[1]);

// TALLAS
if ($m = match_route('#^/api/tallas$#', $uri, $method, 'GET'))    TallaController::index();
if ($m = match_route('#^/api/tallas$#', $uri, $method, 'POST'))   TallaController::store();
if ($m = match_route('#^/api/tallas/(\d+)$#', $uri, $method, 'GET'))    TallaController::show((int)$m[0]);
if ($m = match_route('#^/api/tallas/(\d+)$#', $uri, $method, 'PUT'))    TallaController::update((int)$m[0]);
if ($m = match_route('#^/api/tallas/(\d+)$#', $uri, $method, 'PATCH'))  TallaController::update((int)$m[0]);
if ($m = match_route('#^/api/tallas/(\d+)$#', $uri, $method, 'DELETE')) TallaController::destroy((int)$m[0]);

// 404
http_response_code(404);
echo json_encode(['message' => "Ruta no encontrada: [{$method}] {$uri}", 'status' => 404]);
exit;
EOF
