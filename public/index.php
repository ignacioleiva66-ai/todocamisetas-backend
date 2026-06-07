cat > public/index.php << 'EOF'
<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

spl_autoload_register(function (string $class): void {
    $map = [
        'Database'           => __DIR__ . '/../config/Database.php',
        'Response'           => __DIR__ . '/../helpers/Response.php',
        'Validator'          => __DIR__ . '/../helpers/Validator.php',
        'CamisetaModel'      => __DIR__ . '/../models/CamisetaModel.php',
        'ClienteModel'       => __DIR__ . '/../models/ClienteModel.php',
        'TallaModel'         => __DIR__ . '/../models/TallaModel.php',
        'CamisetaController' => __DIR__ . '/../controllers/CamisetaController.php',
        'ClienteController'  => __DIR__ . '/../controllers/ClienteController.php',
        'TallaController'    => __DIR__ . '/../controllers/TallaController.php',
    ];
    if (isset($map[$class])) {
        require_once $map[$class];
    }
});

require_once __DIR__ . '/../routes/api.php';
EOF
