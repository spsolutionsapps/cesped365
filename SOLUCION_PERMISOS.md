# 🔧 Solución: No se generan logs (Problema de Permisos)

## 🐛 **Problema**

No se están generando archivos de log en `public_html/api/writable/logs/`

**Esto indica que:**
- CodeIgniter **NO puede escribir** en la carpeta `writable/`
- El error 500 es por permisos, no por código
- PHP no tiene acceso de escritura

---

## ✅ **Solución: Cambiar Permisos**

### **Método 1: Vía cPanel File Manager** (Más fácil)

1. **cPanel → File Manager**

2. **Navegar a:** `public_html/api/`

3. **Click derecho en la carpeta `writable`** → **Change Permissions**

4. **Configurar permisos a `777`** (temporalmente para testing):
   - Marcar TODAS las casillas
   - Valor numérico: **777**
   - ✅ Marcar: **"Recurse into subdirectories"**
   - Click en **"Change Permissions"**

5. **Repetir para cada subcarpeta:**
   - `writable/cache/` → 777
   - `writable/logs/` → 777
   - `writable/session/` → 777
   - `writable/uploads/` → 777

---

### **Método 2: Vía FTP (FileZilla)**

1. **Conectar con FileZilla**

2. **Ir a:** `public_html/api/writable/`

3. **Click derecho en `writable`** → **File permissions**

4. **Numeric value: 777**
   - Marcar: Read, Write, Execute para Owner, Group, Public
   - ✅ Marcar: **"Recurse into subdirectories"**
   - ✅ Marcar: **"Apply to directories only"**
   - Click **OK**

5. **Repetir pero para archivos:**
   - Numeric value: **666**
   - ✅ Marcar: **"Apply to files only"**

---

### **Método 3: Crear script PHP para verificar permisos**

He creado un archivo de diagnóstico. Sube este archivo a tu servidor:

**Archivo: `test-permisos.php`**

```php
<?php
// Test de permisos - Subir a public_html/api/test-permisos.php
// Visitar: https://cesped365.com/api/test-permisos.php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test de Permisos - Cesped365</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: #059669; background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #d97706; background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test de Permisos - Cesped365</h1>
        
        <?php
        $baseDir = __DIR__;
        $writableDir = $baseDir . '/writable';
        $testFile = $writableDir . '/logs/test-permissions.txt';
        
        echo "<h2>📁 Información del Sistema</h2>";
        echo "<pre>";
        echo "PHP User: " . get_current_user() . "\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Base Dir: " . $baseDir . "\n";
        echo "Writable Dir: " . $writableDir . "\n";
        echo "</pre>";
        
        echo "<h2>🔧 Permisos de Carpetas</h2>";
        
        $folders = [
            'writable' => $writableDir,
            'writable/cache' => $writableDir . '/cache',
            'writable/logs' => $writableDir . '/logs',
            'writable/session' => $writableDir . '/session',
            'writable/uploads' => $writableDir . '/uploads',
        ];
        
        foreach ($folders as $name => $path) {
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -3);
                $isWritable = is_writable($path);
                
                if ($isWritable) {
                    echo "<div class='success'>✅ <strong>$name/</strong> - Permisos: $perms - ESCRIBIBLE</div>";
                } else {
                    echo "<div class='error'>❌ <strong>$name/</strong> - Permisos: $perms - NO ESCRIBIBLE</div>";
                    echo "<div class='warning'>Solución: Cambiar permisos a 755 o 777</div>";
                }
            } else {
                echo "<div class='error'>❌ <strong>$name/</strong> - NO EXISTE</div>";
            }
        }
        
        echo "<h2>🧪 Test de Escritura</h2>";
        
        // Test 1: Crear directorio
        $testDir = $writableDir . '/logs';
        if (!file_exists($testDir)) {
            if (@mkdir($testDir, 0777, true)) {
                echo "<div class='success'>✅ Se pudo crear el directorio logs/</div>";
            } else {
                echo "<div class='error'>❌ NO se pudo crear el directorio logs/</div>";
            }
        } else {
            echo "<div class='success'>✅ El directorio logs/ existe</div>";
        }
        
        // Test 2: Escribir archivo
        $testContent = "Test de escritura - " . date('Y-m-d H:i:s');
        if (@file_put_contents($testFile, $testContent)) {
            echo "<div class='success'>✅ Se pudo escribir archivo: $testFile</div>";
            
            // Leer el archivo
            $content = file_get_contents($testFile);
            echo "<div class='success'>✅ Se pudo leer el archivo. Contenido: $content</div>";
            
            // Eliminar archivo de test
            @unlink($testFile);
        } else {
            echo "<div class='error'>❌ NO se pudo escribir archivo en: $testFile</div>";
            echo "<div class='error'>Error: " . error_get_last()['message'] . "</div>";
        }
        
        echo "<h2>🎯 Recomendaciones</h2>";
        
        $allWritable = true;
        foreach ($folders as $path) {
            if (!is_writable($path)) {
                $allWritable = false;
                break;
            }
        }
        
        if ($allWritable) {
            echo "<div class='success'>✅ Todas las carpetas son escribibles. El problema puede ser otro.</div>";
        } else {
            echo "<div class='warning'>";
            echo "<strong>⚠️ Hay carpetas sin permisos de escritura.</strong><br><br>";
            echo "<strong>Solución en cPanel:</strong><br>";
            echo "1. File Manager → public_html/api/writable/<br>";
            echo "2. Click derecho → Change Permissions<br>";
            echo "3. Poner: 777 (temporalmente)<br>";
            echo "4. Marcar: Recurse into subdirectories<br>";
            echo "5. Click: Change Permissions<br><br>";
            echo "<strong>Después cambiar a 755 por seguridad</strong>";
            echo "</div>";
        }
        ?>
        
        <div class="warning" style="margin-top: 20px;">
            <strong>🔒 IMPORTANTE:</strong> Después de que funcione todo, cambiar permisos a <strong>755</strong> por seguridad.<br>
            777 = Todo el mundo puede escribir (menos seguro)<br>
            755 = Solo el dueño puede escribir (más seguro)
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px;">
            <strong>⚠️ BORRAR ESTE ARCHIVO después de usarlo:</strong><br>
            <code>public_html/api/test-permisos.php</code>
        </div>
    </div>
</body>
</html>
```

---

## 📋 **Permisos Recomendados**

| Carpeta/Archivo | Permisos | Código |
|-----------------|----------|--------|
| `api/` | Solo lectura | 755 |
| `api/writable/` | Lectura + Escritura | **777** (temporal) → **755** (final) |
| `api/writable/cache/` | Lectura + Escritura | **777** (temporal) → **755** (final) |
| `api/writable/logs/` | Lectura + Escritura | **777** (temporal) → **755** (final) |
| `api/writable/session/` | Lectura + Escritura | **777** (temporal) → **755** (final) |
| `api/writable/uploads/` | Lectura + Escritura | **777** (temporal) → **755** (final) |
| Archivos `.php` | Solo lectura | 644 |

---

## 🔢 **Explicación de Permisos**

```
777 = rwxrwxrwx = Todos pueden leer, escribir, ejecutar
755 = rwxr-xr-x = Dueño puede escribir, otros solo leer
644 = rw-r--r-- = Dueño puede escribir, otros solo leer
```

**Para archivos:**
- `644` = Normal para archivos PHP/HTML
- `666` = Si necesitas que se puedan editar vía web

**Para carpetas:**
- `755` = Normal, seguro
- `777` = Menos seguro, pero a veces necesario en hosting compartido

---

## 🎯 **Pasos Inmediatos**

1. **Cambiar permisos de `writable/` a 777:**
   - cPanel → File Manager
   - `public_html/api/writable/` → Click derecho → Change Permissions
   - Poner **777**
   - Marcar **"Recurse into subdirectories"**

2. **Probar login de nuevo**

3. **Verificar que se creó el log:**
   - Ir a `public_html/api/writable/logs/`
   - Debe aparecer: `log-2026-01-13.log`

4. **Leer el log** para ver el error específico

---

## 🐛 **Posibles Mensajes de Error**

Si después de cambiar permisos sigue sin crear logs:

### **Causa 1: PHP ejecutándose como usuario diferente**

Algunos hostings baratos ejecutan PHP como un usuario diferente.

**Solución:**
- Cambiar permisos a **777** (menos seguro pero funciona)
- O contactar soporte del hosting

### **Causa 2: `writable/` apunta a otra ubicación**

En `api/app/Config/Paths.php`, verifica que `WRITEPATH` esté correcto.

### **Causa 3: Safe Mode o Open Base Dir**

Algunos hostings restringen la escritura de archivos.

**Verificar en phpMyAdmin o `diagnostico.php`:**
```php
<?php
echo "Safe Mode: " . (ini_get('safe_mode') ? 'ON' : 'OFF');
echo "\nOpen BaseDir: " . ini_get('open_basedir');
?>
```

---

## 📞 **Siguiente Paso**

**Cambia los permisos a 777 y prueba de nuevo.**

Si sigue sin funcionar:
1. Sube `test-permisos.php` a `public_html/api/`
2. Visita: `https://cesped365.com/api/test-permisos.php`
3. Envíame un screenshot de lo que aparece

---

**¿Ya cambiaste los permisos de `writable/` a 777?**
