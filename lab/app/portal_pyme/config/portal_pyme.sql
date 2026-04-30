DROP DATABASE IF EXISTS portal_pyme;
CREATE DATABASE portal_pyme CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_pyme;

CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL,
    rol VARCHAR(30) NOT NULL,
    departamento VARCHAR(80) NOT NULL
);

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa VARCHAR(120) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    telefono VARCHAR(40) NOT NULL,
    owner_id INT NOT NULL,
    notas TEXT,
    FOREIGN KEY (owner_id) REFERENCES empleados(id)
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    asunto VARCHAR(160) NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(40) NOT NULL,
    prioridad VARCHAR(30) NOT NULL,
    empleado_asignado VARCHAR(80) NOT NULL,
    cliente_id INT NULL
);

CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(160) NOT NULL,
    tipo VARCHAR(40) NOT NULL,
    descripcion TEXT NOT NULL,
    ruta VARCHAR(255) NOT NULL
);

CREATE TABLE perfil_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_guardado VARCHAR(255) NOT NULL,
    ruta_web VARCHAR(255) NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id)
);

INSERT INTO empleados (id, username, password, email, rol, departamento) VALUES
(1, 'superadmin', 'SuperPYME2026!', 'superadmin@portalpyme.local', 'admin', 'Direccion'),
(2, 'soporte', 'soporte123', 'soporte@portalpyme.local', 'empleado', 'Soporte'),
(3, 'jtorre', 'Invierno2026', 'j.torre@portalpyme.local', 'empleado', 'Comercial'),
(4, 'mgarcia', 'clientes2026', 'm.garcia@portalpyme.local', 'empleado', 'Comercial'),
(5, 'contabilidad', 'Conta2026!', 'contabilidad@portalpyme.local', 'empleado', 'Administracion'),
(6, 'juan', 'juan123', 'juan@portalpyme.local', 'empleado', 'Soporte');

INSERT INTO clientes (id, empresa, nombre, email, telefono, owner_id, notas) VALUES
(1, 'Norte Digital SL', 'Ana Ruiz', 'ana.ruiz@nortedigital.local', '+34 600 111 001', 3, 'Cliente asignado a jtorre. Renovación prevista en mayo.'),
(2, 'Logistica Campo SA', 'Carlos Medina', 'c.medina@logcampo.local', '+34 600 111 002', 3, 'Incidencia recurrente con acceso VPN. Revisar ticket TCK-2026-002.'),
(3, 'Clinica Alameda', 'Laura Perez', 'laura.perez@alameda.local', '+34 600 111 003', 4, 'Cliente gestionado por mgarcia. No compartir datos comerciales.'),
(4, 'Talleres Rivas', 'Miguel Rivas', 'miguel@talleresrivas.local', '+34 600 111 004', 4, 'Nota interna: pidió reset de contraseña del portal antiguo.'),
(5, 'Grupo Lince', 'Sergio Martin', 'sergio.martin@grupolince.local', '+34 600 111 005', 2, 'Cliente prioritario. Contactar solo desde soporte.'),
(6, 'Backup Services Iberia', 'Elena Casas', 'elena@backupiberia.local', '+34 600 111 006', 1, 'Cuenta sensible. Revisar documentación histórica de backups.');

INSERT INTO tickets (id, codigo, asunto, descripcion, estado, prioridad, empleado_asignado, cliente_id) VALUES
(1, 'TCK-2026-001', 'Error al acceder al portal', 'El cliente indica error intermitente al acceder desde la oficina.', 'Abierto', 'Media', 'soporte', 1),
(2, 'TCK-2026-002', 'VPN no conecta', 'Revisar configuración legacy. Posible conflicto con credenciales antiguas.', 'En revisión', 'Alta', 'jtorre', 2),
(3, 'TCK-2026-003', 'Solicitud de factura', 'Cliente solicita duplicado de factura trimestral.', 'Cerrado', 'Baja', 'contabilidad', 3),
(4, 'TCK-2026-004', 'Migración de backup', 'Pendiente mover backup fuera del DocumentRoot.', 'Abierto', 'Alta', 'superadmin', 6),
(5, 'TCK-2026-005', 'Alta nuevo empleado', 'Crear usuario temporal para soporte de guardia.', 'En revisión', 'Media', 'soporte', 5);

INSERT INTO documentos (id, nombre, tipo, descripcion, ruta) VALUES
(1, 'Manual de onboarding', 'TXT', 'Documento básico para nuevos empleados.', '/portal_pyme/uploads/manual_onboarding.txt'),
(2, 'Política de contraseñas', 'TXT', 'Borrador pendiente de revisión por administración.', '/portal_pyme/uploads/politica_passwords.txt'),
(3, 'Backup histórico', 'SQL', 'Copia parcial del portal antiguo. Pendiente de retirada.', '/portal_pyme/backup/portal_backup.sql');
