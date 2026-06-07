cat > controllers/ClienteController.php << 'EOF'
<?php
declare(strict_types=1);

class ClienteController
{
    private const CATEGORIAS = ['Regular', 'Preferencial'];

    public static function index(): void
    {
        $c = ClienteModel::all();
        Response::json($c, 200, ['total' => count($c)]);
    }

    public static function show(int $id): void
    {
        $c = ClienteModel::find($id);
        if (!$c) Response::notFound('Cliente no encontrado.');
        Response::json($c);
    }

    public static function store(): void
    {
        $b = self::body();
        $v = new Validator($b);
        $v->required('nombre_comercial')->required('rut')->required('direccion')
          ->required('contacto_nombre')->required('contacto_email')
          ->email('contacto_email')->inList('categoria', self::CATEGORIAS)->numeric('porcentaje_oferta', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (ClienteModel::existeRut($b['rut'])) Response::error('El RUT ya está registrado.', 422);
        Response::created(ClienteModel::create($b), 'Cliente creado.');
    }

    public static function update(int $id): void
    {
        if (!ClienteModel::find($id)) Response::notFound('Cliente no encontrado.');
        $b = self::body();
        $v = new Validator($b);
        $v->required('nombre_comercial')->required('rut')->required('direccion')
          ->required('contacto_nombre')->required('contacto_email')
          ->email('contacto_email')->inList('categoria', self::CATEGORIAS)->numeric('porcentaje_oferta', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (isset($b['rut']) && ClienteModel::existeRut($b['rut'], $id)) Response::error('El RUT ya está registrado.', 422);
        ClienteModel::update($id, $b);
        Response::json(ClienteModel::find($id));
    }

    public static function patch(int $id): void
    {
        if (!ClienteModel::find($id)) Response::notFound('Cliente no encontrado.');
        $b = self::body();
        $v = new Validator($b);
        if (isset($b['contacto_email'])) $v->email('contacto_email');
        if (isset($b['categoria']))       $v->inList('categoria', self::CATEGORIAS);
        if (isset($b['porcentaje_oferta'])) $v->numeric('porcentaje_oferta', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (isset($b['rut']) && ClienteModel::existeRut($b['rut'], $id)) Response::error('El RUT ya está registrado.', 422);
        ClienteModel::update($id, $b);
        Response::json(ClienteModel::find($id));
    }

    public static function destroy(int $id): void
    {
        if (!ClienteModel::find($id)) Response::notFound('Cliente no encontrado.');
        try { ClienteModel::destroy($id); Response::message('Cliente eliminado.'); }
        catch (RuntimeException $e) { Response::error($e->getMessage(), 409); }
    }

    private static function body(): array
    {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }
}
EOF
