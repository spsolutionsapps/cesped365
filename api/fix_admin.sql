-- Script SQL para verificar/crear/actualizar el usuario admin
-- Ejecutar: mysql -u root -p cesped365 < fix_admin.sql

-- Verificar si existe el usuario admin
SELECT id, name, email, role FROM users WHERE email = 'admin@cesped365.com';

-- Si no existe, crear el usuario admin
INSERT INTO users (name, email, password, role, created_at, updated_at)
SELECT 
    'Administrador',
    'admin@cesped365.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash de 'admin123'
    'admin',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@cesped365.com'
);

-- Si existe pero la contraseña no funciona, actualizarla
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash de 'admin123'
    updated_at = NOW()
WHERE email = 'admin@cesped365.com';

-- Verificar el resultado
SELECT id, name, email, role, LEFT(password, 20) as password_hash_preview FROM users WHERE email = 'admin@cesped365.com';
