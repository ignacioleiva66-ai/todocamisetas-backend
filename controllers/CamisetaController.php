cat > controllers/CamisetaController.php << 'EOF'
<?php
declare(strict_types=1);

class CamisetaController
{
    private const TIPOS = ['Local', 'Visita', '3era Camiseta', 'Femenino Local', 'Niño'];

    public static function index(): void
    {
        $c = CamisetaModel::all();
        Response::json($c, 200, ['total' => count($c)]);
    }

    public static function show(int $id): void
    {
        $c = CamisetaModel::find($id);
        if (!$c) Response::notFound('Camiseta no encontrada.');
        Response::json($c);
    }

    public static function store(): void
    {
        $b = self::body();
        $v = new Validator($b);
        $v->required('titulo')->required('club')->required('pais')->required('tipo')
          ->required('color')->required('precio')->required('codigo_producto')
          ->inList('tipo', self::TIPOS)->numeric('precio', 0)->numeric('precio_oferta', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (CamisetaModel::existeCodigo($b['codigo_producto'])) Response::error('El SKU ya existe.', 422);
        Response::created(CamisetaModel::create($b), 'Camiseta creada.');
    }

    public static function update(int $id): void
    {
        if (!CamisetaModel::find($id)) Response::notFound('Camiseta no encontrada.');
        $b = self::body();
        $v = new Validator($b);
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $v->required('titulo')->required('club')->required('pais')
              ->required('tipo')->required('color')->required('precio')->required('codigo_producto');
        }
        if (isset($b['tipo']))           $v->inList('tipo', self::TIPOS);
        if (isset($b['precio']))         $v->numeric('precio', 0);
        if (isset($b['precio_oferta']))  $v->numeric('precio_oferta', 0);
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (isset($b['codigo_producto']) && CamisetaModel::existeCodigo($b['codigo_producto'], $id))
            Response::error('El SKU ya existe.', 422);
        CamisetaModel::update($id, $b);
        Response::json(CamisetaModel::find($id));
    }

    public static function destroy(int $id): void
    {
        if (!CamisetaModel::find($id)) Response::notFound('Camiseta no encontrada.');
        CamisetaModel::destroy($id);
        Response::message('Camiseta eliminada.');
    }

    public static function precioFinal(int $id): void
    {
        $c = CamisetaModel::find($id);
        if (!$c) Response::notFound('Camiseta no encontrada.');
        $clienteId = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
        if (!$clienteId) Response::error('Parámetro cliente_id requerido.', 422);
        $cliente = ClienteModel::find($clienteId);
        if (!$cliente) Response::notFound('Cliente no encontrado.');
        $r = CamisetaModel::calcularPrecioFinal($c, $cliente);
        Response::json(array_merge($c, [
            'precio_final'              => $r['precio_final'],
            'descuento_aplicado'        => $r['descuento_aplicado'],
            'cliente_id'                => $cliente['id'],
            'cliente_nombre'            => $cliente['nombre_comercial'],
            'cliente_categoria'         => $cliente['categoria'],
            'cliente_porcentaje_oferta' => $cliente['porcentaje_oferta'],
        ]));
    }

    public static function porCliente(int $clienteId): void
    {
        $cliente = ClienteModel::find($clienteId);
        if (!$cliente) Response::notFound('Cliente no encontrado.');
        $camisetas = CamisetaModel::porCliente($clienteId, $cliente);
        Response::json($camisetas, 200, ['cliente' => $cliente['nombre_comercial'], 'total' => count($camisetas)]);
    }

    public static function asociarCliente(int $clienteId): void
    {
        if (!ClienteModel::find($clienteId)) Response::notFound('Cliente no encontrado.');
        $b = self::body();
        $v = new Validator($b);
        $v->required('camiseta_id');
        if ($v->fails()) Response::error('Error de validación.', 422, $v->errors());
        if (!CamisetaModel::find((int)$b['camiseta_id'])) Response::notFound('Camiseta no encontrada.');
        CamisetaModel::asociarCliente($clienteId, (int)$b['camiseta_id']);
        Response::message('Camiseta asociada al cliente.');
    }

    public static function desasociarCliente(int $clienteId, int $camisetaId): void
    {
        if (!ClienteModel::find($clienteId))   Response::notFound('Cliente no encontrado.');
        if (!CamisetaModel::find($camisetaId)) Response::notFound('Camiseta no encontrada.');
        CamisetaModel::desasociarCliente($clienteId, $camisetaId);
        Response::message('Camiseta desasociada del cliente.');
    }

    private static function body(): array
    {
        $d = json_decode(file_get_contents('php://input'), true);
        return is_array($d) ? $d : [];
    }
}
EOF
