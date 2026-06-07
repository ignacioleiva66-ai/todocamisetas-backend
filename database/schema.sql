cat > database/schema.sql << 'EOF'
CREATE TABLE IF NOT EXISTS clientes (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_comercial  VARCHAR(255) NOT NULL,
    rut               VARCHAR(20) NOT NULL UNIQUE,
    direccion         VARCHAR(255) NOT NULL,
    categoria         ENUM('Regular','Preferencial') NOT NULL DEFAULT 'Regular',
    contacto_nombre   VARCHAR(255) NOT NULL,
    contacto_email    VARCHAR(255) NOT NULL,
    porcentaje_oferta DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tallas (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(10) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS camisetas (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo           VARCHAR(255) NOT NULL,
    club             VARCHAR(255) NOT NULL,
    pais             VARCHAR(100) NOT NULL,
    tipo             ENUM('Local','Visita','3era Camiseta','Femenino Local','Niño') NOT NULL,
    color            VARCHAR(100) NOT NULL,
    precio           DECIMAL(10,2) NOT NULL,
    precio_oferta    DECIMAL(10,2) NULL DEFAULT NULL,
    detalles         TEXT NULL DEFAULT NULL,
    codigo_producto  VARCHAR(50) NOT NULL UNIQUE,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS camiseta_talla (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    camiseta_id INT UNSIGNED NOT NULL,
    talla_id    INT UNSIGNED NOT NULL,
    stock       INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_camiseta_talla (camiseta_id, talla_id),
    CONSTRAINT fk_ct_cam FOREIGN KEY (camiseta_id) REFERENCES camisetas(id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_tal FOREIGN KEY (talla_id)    REFERENCES tallas(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cliente_camiseta (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT UNSIGNED NOT NULL,
    camiseta_id INT UNSIGNED NOT NULL,
    cantidad    INT NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cc_cl FOREIGN KEY (cliente_id)  REFERENCES clientes(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_cc_ca FOREIGN KEY (camiseta_id) REFERENCES camisetas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO tallas (nombre) VALUES ('XS'),('S'),('M'),('L'),('XL'),('XXL');

INSERT IGNORE INTO clientes (nombre_comercial, rut, direccion, categoria, contacto_nombre, contacto_email, porcentaje_oferta) VALUES
('90minutos', '76.111.222-3', 'Providencia, Santiago', 'Preferencial', 'Carlos Ruiz', 'carlos@90minutos.cl', 15.00),
('tdeportes', '76.333.444-5', 'Las Condes, Santiago',  'Regular',      'Ana Torres',  'ana@tdeportes.cl',    0.00);

INSERT IGNORE INTO camisetas (titulo, club, pais, tipo, color, precio, precio_oferta, detalles, codigo_producto) VALUES
('Camiseta Local 2025 - Seleccion Chilena', 'Seleccion Chilena', 'Chile', 'Local', 'Blanco y Rojo', 45000, 38000, 'Edicion aniversario 2025', 'SCL2025L'),
('Camiseta Visita 2025 - Colo-Colo',        'Colo-Colo',         'Chile', 'Visita','Negro',          38000,  NULL, NULL,                       'CCVISI2025'),
('Camiseta Local 2025 - U de Chile',        'U de Chile',        'Chile', 'Local', 'Azul',           40000, 34000, NULL,                       'UCH2025L'),
('Camiseta Femenina - Seleccion Chilena',   'Seleccion Chilena', 'Chile', 'Femenino Local','Rojo y Blanco', 35000, NULL, 'Edicion especial', 'SCL2025FEM'),
('Camiseta Nino - Real Madrid',             'Real Madrid',       'Espana','Nino',  'Blanco',         29000, 24000, NULL,                       'RMNINO2025');

INSERT IGNORE INTO camiseta_talla (camiseta_id, talla_id, stock)
SELECT c.id, t.id, 20 FROM camisetas c, tallas t
WHERE c.codigo_producto IN ('SCL2025L','CCVISI2025','UCH2025L') AND t.nombre IN ('S','M','L','XL');

INSERT IGNORE INTO cliente_camiseta (cliente_id, camiseta_id)
SELECT cl.id, ca.id FROM clientes cl, camisetas ca
WHERE cl.nombre_comercial = '90minutos' AND ca.codigo_producto IN ('SCL2025L','UCH2025L','RMNINO2025');

INSERT IGNORE INTO cliente_camiseta (cliente_id, camiseta_id)
SELECT cl.id, ca.id FROM clientes cl, camisetas ca
WHERE cl.nombre_comercial = 'tdeportes' AND ca.codigo_producto IN ('CCVISI2025','SCL2025FEM');
EOF
