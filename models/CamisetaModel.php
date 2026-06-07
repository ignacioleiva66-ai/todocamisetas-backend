cat > models/CamisetaModel.php << 'EOF'
<?php
declare(strict_types=1);

class CamisetaModel
{
    public static function all(): array
    {
        $rows = Database::getInstance()->query('SELECT * FROM camisetas WHERE deleted_at IS NULL ORDER BY id')->fetchAll();
        return self::adjuntarTallas($rows);
    }

    public static function find(int $id): ?array
    {
        $s = Database::getInstance()->prepare('SELECT * FROM camisetas WHERE id = :id AND deleted_at IS NULL');
        $s->execute([':id' => $id]);
        $row = $s->fetch();
        if (!$row) return null;
        $row['tallas'] = TallaModel::porCamiseta($row['id']);
        return $row;
    }

    public static function create(array $data): array
    {
        $db = Database::getInstance();
        $db->prepare(
            'INSERT INTO camisetas (titulo, club, pais, tipo, color, precio, precio_oferta, detalles, codigo_producto, created_at, updated_at)
             VALUES (:titulo, :club, :pais, :tipo, :color, :precio, :po, :det, :sku, NOW(), NOW())'
        )->execute([
            ':titulo' => $data['titulo'], ':club' => $data['club'], ':pais' => $data['pais'],
            ':tipo'   => $data['tipo'],   ':color' => $data['color'], ':precio' => $data['precio'],
            ':po'     => $data['precio_oferta'] ?? null,
            ':det'    => $data['detalles'] ?? null,
            ':sku'    => $data['codigo_producto'],
        ]);
        return self::find((int)$db->lastInsertId());
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        $campos = []; $vals = [':id' => $id];
        foreach (['titulo','club','pais','tipo','color','precio','precio_oferta','detalles','codigo_producto'] as $f) {
            if (array_key_exists($f, $data)) { $campos[] = "$f = :$f"; $vals[":$f"] = $data[$f]; }
        }
        if (empty($campos)) return false;
        $campos[] = 'updated_at = NOW()';
        return $db->prepare('UPDATE camisetas SET ' . implode(', ', $campos) . ' WHERE id = :id AND deleted_at IS NULL')->execute($vals);
    }

    public static function destroy(int $id): bool
    {
        $db = Database::getInstance();
        $db->prepare('DELETE FROM camiseta_talla WHERE camiseta_id = :id')->execute([':id' => $id]);
        $db->prepare('DELETE FROM cliente_camiseta WHERE camiseta_id = :id')->execute([':id' => $id]);
        $s = $db->prepare('UPDATE camisetas SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $s->execute([':id' => $id]);
        return $s->rowCount() > 0;
    }

    public static function porCliente(int $clienteId, array $cliente): array
    {
        $s = Database::getInstance()->prepare(
            'SELECT c.* FROM camisetas c
             INNER JOIN cliente_camiseta cc ON cc.camiseta_id = c.id
             WHERE cc.cliente_id = :cid AND c.deleted_at IS NULL ORDER BY c.id'
        );
        $s->execute([':cid' => $clienteId]);
        $rows = self::adjuntarTallas($s->fetchAll());
        return array_map(function($c) use ($cliente) {
            $r = self::calcularPrecioFinal($c, $cliente);
            return array_merge($c, ['precio_final' => $r['precio_final'], 'descuento_aplicado' => $r['descuento_aplicado']]);
        }, $rows);
    }

    public static function asociarCliente(int $clienteId, int $camisetaId): bool
    {
        return Database::getInstance()->prepare(
            'INSERT IGNORE INTO cliente_camiseta (cliente_id, camiseta_id, created_at, updated_at) VALUES (:cl, :ca, NOW(), NOW())'
        )->execute([':cl' => $clienteId, ':ca' => $camisetaId]);
    }

    public static function desasociarCliente(int $clienteId, int $camisetaId): bool
    {
        return Database::getInstance()->prepare(
            'DELETE FROM cliente_camiseta WHERE cliente_id = :cl AND camiseta_id = :ca'
        )->execute([':cl' => $clienteId, ':ca' => $camisetaId]);
    }

    public static function calcularPrecioFinal(array $camiseta, array $cliente): array
    {
        $base = (float)$camiseta['precio'];
        $candidatos = [];
        if ($cliente['categoria'] === 'Preferencial' && !empty($camiseta['precio_oferta'])) {
            $candidatos['precio_oferta'] = (float)$camiseta['precio_oferta'];
        }
        if ((float)$cliente['porcentaje_oferta'] > 0) {
            $candidatos['porcentaje_oferta'] = round($base * (1 - (float)$cliente['porcentaje_oferta'] / 100), 0);
        }
        if (empty($candidatos)) return ['precio_final' => $base, 'descuento_aplicado' => 'ninguno'];
        $precioFinal = min($candidatos);
        return ['precio_final' => $precioFinal, 'descuento_aplicado' => array_search($precioFinal, $candidatos)];
    }

    public static function existeCodigo(string $codigo, ?int $excludeId = null): bool
    {
        $db = Database::getInstance();
        if ($excludeId !== null) {
            $s = $db->prepare('SELECT COUNT(*) FROM camisetas WHERE codigo_producto = :c AND id != :id AND deleted_at IS NULL');
            $s->execute([':c' => $codigo, ':id' => $excludeId]);
        } else {
            $s = $db->prepare('SELECT COUNT(*) FROM camisetas WHERE codigo_producto = :c AND deleted_at IS NULL');
            $s->execute([':c' => $codigo]);
        }
        return (int)$s->fetchColumn() > 0;
    }

    private static function adjuntarTallas(array $camisetas): array
    {
        if (empty($camisetas)) return [];
        $ids = array_column($camisetas, 'id');
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $s   = Database::getInstance()->prepare(
            "SELECT t.id, t.nombre, ct.stock, ct.camiseta_id FROM tallas t
             INNER JOIN camiseta_talla ct ON ct.talla_id = t.id
             WHERE ct.camiseta_id IN ($in) ORDER BY t.nombre"
        );
        $s->execute($ids);
        $map = [];
        foreach ($s->fetchAll() as $t) { $cid = $t['camiseta_id']; unset($t['camiseta_id']); $map[$cid][] = $t; }
        foreach ($camisetas as &$c) $c['tallas'] = $map[$c['id']] ?? [];
        return $camisetas;
    }
}
EOF
