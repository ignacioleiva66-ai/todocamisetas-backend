cat > helpers/Response.php << 'EOF'
<?php
declare(strict_types=1);

class Response
{
    public static function json(mixed $data, int $status = 200, array $extra = []): never
    {
        http_response_code($status);
        echo json_encode(array_merge(['data' => $data], $extra), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $status = 400, array $errors = []): never
    {
        http_response_code($status);
        $body = ['message' => $message];
        if (!empty($errors)) $body['errors'] = $errors;
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function notFound(string $message = 'Recurso no encontrado.'): never
    {
        self::error($message, 404);
    }

    public static function created(mixed $data, string $message = 'Creado correctamente.'): never
    {
        http_response_code(201);
        echo json_encode(['data' => $data, 'message' => $message], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function message(string $message, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
EOF
