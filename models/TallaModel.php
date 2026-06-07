cat > models/TallaModel.php << 'EOF'
<?php
declare(strict_types=1);

class TallaModel
{
    public static function all(): array
    {
        return Database::getInstance()->query('SELECT * FROM tallas ORDER BY id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $s = Database::getInstance()->prepare('SELECT * FROM tallas WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public static function create(array $data): array
    {
        $db = Database::getInstance();
        $db->prepare('INSERT INTO tallas (nombre, created_at, updated_at) VALUES (:nombre, NOW(), NOW())')
           ->execute([':nombre' => trim($data['nombre'])]);
        return self::find((int)$db->lastInsertId());
    }

    public static function update(int $id, array $data): bool
    {
        return Database::getInstance()
            ->prepare('UPDATE tallas SET nombre = :nombre, updated_at = NOW() WHERE id = :id')
            ->execute([':nombre' => trim($data['nombre']), ':id' => $id]);
    }

    public static function destroy(int $id): bool
    {
        $db = Database::getInstance();
        $c = $db->prepare('SELECT COUNT(*) FROM camiseta_talla WHERE talla_id = :id');
        $c->execute([':id' => $id]);
        if ((int)$c->fetchColumn() > 0) throw new RuntimeException('No se puede eliminar: talla en uso.');
        return $db->prepare('DELETE FROM tallas WHERE id = :id')->execute([':id' => $id]);
    }

    public static function porCamiseta(int $camisetaId): array
    {
        $s = Database::getInstance()->prepare(
            'SELECT t.id, t.nombre, ct.stock FROM tallas t
             INNER JOIN camiseta_talla ct ON ct.talla_id = t.id
             WHERE ct.camiseta_id = :id ORDER BY t.nombre'
        );
        $s->execute([':id' => $camisetaId]);
        return $s->fetchAll();
    }

    public static function asociar(int $camisetaId, int $tallaId, int $stock): bool
    {
        return Database::getInstance()->prepare(
            'INSERT INTO camiseta_talla (camiseta_id, talla_id, stock, created_at, updated_at)
             VALUES (:c, :t, :s, NOW(), NOW())
             ON DUPLICATE KEY UPDATE stock = :s2, updated_at = NOW()'
        )->execute([':c' => $camisetaId, ':t' => $tallaId, ':s' => $stock, ':s2' => $stock]);
    }

    public static function existeNombre(string $nombre, ?int $excludeId = null): bool
    {
        $db = Database::getInstance();
        if ($excludeId !== null) {
            $s = $db->prepare('SELECT COUNT(*) FROM tallas WHERE nombre = :n AND id != :id');
            $s->execute([':n' => $nombre, ':id' => $excludeId]);
        } else {
            $s = $db->prepare('SELECT COUNT(*) FROM tallas WHERE nombre = :n');
            $s->execute([':n' => $nombre]);
        }
        return (int)$s->fetchColumn() > 0;
    }
}
EOF
