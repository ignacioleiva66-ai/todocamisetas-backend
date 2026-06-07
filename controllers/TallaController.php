cat > controllers/TallaController.php << 'EOF'
<?php
declare(strict_types=1);

class TallaController
{
    public static function index(): void
    {
        $t = TallaModel::all();
        Response::json($t, 200, ['total' => count($t)]);
    }

    public static function show(int $id): void
    {
        $t = TallaModel::find($id);
        if (!$t) Response::notFound('Talla no encontrada.');
        Response::json($t);
    }

    public static function store(): void
    {
        $body = self::body();
        $v = new Validator($body);
        $v->required('nombre', 'nombre')->maxLength('nombre', 10);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        $nombre = strtoupper(trim($body['nombre']));
        if (TallaModel::existeNombre($nombre)) Response::error("La talla '{$nombre}' ya existe.", 422);
        Response::created(TallaModel::create(['nombre' => $nombre]), 'Talla creada.');
    }

    public static function update(int $id): void
    {
        if (!TallaModel::find($id)) Response::notFound('Talla no encontrada.');
        $body = self::body();
        $v = new Validator($body);
        $v->required('nombre', 'nombre')->maxLength('nombre', 10);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        $nombre = strtoupper(trim($body['nombre']));
        if (TallaModel::existeNombre($nombre, $id)) Response::error("La talla '{$nombre}' ya existe.", 422);
        TallaModel::update($id, ['nombre' => $nombre]);
        Response::json(TallaModel::find($id));
    }

    public static function destroy(int $id): void
    {
        if (!TallaModel::find($id)) Response::notFound('Talla no encontrada.');
        try { TallaModel::destroy($id); Response::message('Talla eliminada.'); }
        catch (RuntimeException $e) { Response::error($e->getMessage(), 409); }
    }

    public static function asociarACamiseta(int $camisetaId): void
    {
        if (!CamisetaModel::find($camisetaId)) Response::notFound('Camiseta no encontrada.');
        $body = self::body();
        $v = new Validator($body);
        $v->required('talla_id')->required('stock')->numeric('stock', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (!TallaModel::find((int)$body['talla_id'])) Response::notFound('Talla no encontrada.');
        TallaModel::asociar($camisetaId, (int)$body['talla_id'], (int)$body['stock']);
        Response::json(CamisetaModel::find($camisetaId));
    }

    private static function body(): array
    {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }
}
EOF
