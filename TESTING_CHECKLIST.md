# Lista de Verificación - Sistema de Reportes de Jardín

## Fecha: 2026-01-13

## Estado Antes de las Correcciones
- ❌ No se guardaban las imágenes al crear reportes
- ❌ No se mostraban notificaciones de error
- ❌ Errores de sintaxis en JavaScript en la consola
- ❌ Modal de confirmación no funcionaba

## Estado Después de las Correcciones
- ✅ Sistema de carga de imágenes corregido
- ✅ Notificaciones de error implementadas
- ✅ Errores de sintaxis solucionados
- ✅ Validación del lado del cliente agregada
- ✅ Manejo de errores mejorado en el servidor

---

## Pasos de Prueba

### 1. Limpieza de Caché (COMPLETADO ✅)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan storage:link
```

### 2. Verificar Directorio de Storage (COMPLETADO ✅)
```bash
# Directorio creado: storage/app/public/garden-reports
```

### 3. Pruebas de Creación de Reportes

#### Test 3.1: Crear reporte sin imágenes
**Pasos**:
1. Ir a `/admin/garden-reports`
2. Click en "+ Nuevo Reporte"
3. Llenar todos los campos requeridos
4. NO subir imágenes
5. Click en "Crear Reporte"

**Resultado Esperado**:
- ✓ Reporte se crea exitosamente
- ✓ Se muestra notificación verde: "Reporte creado exitosamente"
- ✓ Redirige a la lista de reportes

#### Test 3.2: Crear reporte con 1 imagen
**Pasos**:
1. Ir a `/admin/garden-reports`
2. Click en "+ Nuevo Reporte"
3. Llenar todos los campos requeridos
4. Subir 1 imagen (JPG/PNG, < 2MB)
5. Verificar que se muestra la vista previa
6. Click en "Crear Reporte"

**Resultado Esperado**:
- ✓ Reporte se crea exitosamente
- ✓ Se muestra notificación: "Reporte creado exitosamente con 1 imagen(es)"
- ✓ La imagen se guarda en la base de datos
- ✓ La imagen es visible al ver el reporte

#### Test 3.3: Crear reporte con múltiples imágenes
**Pasos**:
1. Ir a `/admin/garden-reports`
2. Click en "+ Nuevo Reporte"
3. Llenar todos los campos requeridos
4. Subir 3-6 imágenes
5. Verificar que se muestran todas las vistas previas
6. Verificar el contador "X de 6 imágenes"
7. Click en "Crear Reporte"

**Resultado Esperado**:
- ✓ Reporte se crea exitosamente
- ✓ Notificación muestra el número correcto de imágenes
- ✓ Todas las imágenes se guardan
- ✓ Todas las imágenes son visibles

#### Test 3.4: Intentar subir más de 6 imágenes
**Pasos**:
1. Crear nuevo reporte
2. Intentar subir 7 o más imágenes

**Resultado Esperado**:
- ✓ Se muestran solo las primeras 6 imágenes
- ✓ Se muestra notificación de advertencia: "Máximo 6 imágenes permitidas"

#### Test 3.5: Intentar subir archivo no válido
**Pasos**:
1. Crear nuevo reporte
2. Intentar subir un archivo PDF o Word

**Resultado Esperado**:
- ✓ El archivo no se agrega
- ✓ Se muestra notificación: "Solo se permiten archivos de imagen"

#### Test 3.6: Intentar subir imagen muy grande
**Pasos**:
1. Crear nuevo reporte
2. Intentar subir una imagen > 2MB

**Resultado Esperado**:
- ✓ El archivo no se agrega
- ✓ Se muestra notificación: "Tamaño máximo de archivo: 2 MB"

### 4. Pruebas de Validación

#### Test 4.1: Enviar formulario vacío
**Pasos**:
1. Crear nuevo reporte
2. Dejar campos requeridos vacíos
3. Click en "Crear Reporte"

**Resultado Esperado**:
- ✓ Formulario no se envía
- ✓ Se muestran notificaciones de error para cada campo vacío
- ✓ Los campos se marcan con borde rojo (is-invalid)

#### Test 4.2: Corregir errores y enviar
**Pasos**:
1. Después del Test 4.1
2. Llenar los campos requeridos
3. Click en "Crear Reporte"

**Resultado Esperado**:
- ✓ Formulario se envía correctamente
- ✓ Se muestra notificación: "Guardando reporte..."
- ✓ Reporte se crea exitosamente

### 5. Pruebas de Edición

#### Test 5.1: Editar reporte sin agregar imágenes
**Pasos**:
1. Ir a la lista de reportes
2. Click en "Editar" en cualquier reporte
3. Cambiar solo el estado general
4. Click en "Actualizar Reporte"

**Resultado Esperado**:
- ✓ Reporte se actualiza
- ✓ Notificación: "Reporte actualizado exitosamente"

#### Test 5.2: Agregar imágenes a reporte existente
**Pasos**:
1. Editar un reporte
2. Agregar 2 nuevas imágenes
3. Click en "Actualizar Reporte"

**Resultado Esperado**:
- ✓ Reporte se actualiza
- ✓ Notificación: "Reporte actualizado exitosamente con 2 nueva(s) imagen(es)"
- ✓ Las nuevas imágenes aparecen en la sección "Imágenes Existentes"

#### Test 5.3: Eliminar imagen existente
**Pasos**:
1. Editar un reporte con imágenes
2. Click en el botón rojo de eliminar (X) en una imagen
3. Confirmar en el modal

**Resultado Esperado**:
- ✓ Se muestra modal de confirmación
- ✓ Al confirmar, la imagen desaparece
- ✓ Notificación verde: "Imagen eliminada exitosamente"

### 6. Pruebas de Consola del Navegador

#### Test 6.1: Verificar que no hay errores de JavaScript
**Pasos**:
1. Abrir DevTools (F12)
2. Ir a la pestaña Console
3. Navegar por crear/editar reportes
4. Subir/eliminar imágenes

**Resultado Esperado**:
- ✓ No aparece error: `Uncaught SyntaxError: Unexpected token '&'`
- ✓ No aparece error: `Cannot read properties of null (reading 'classList')`
- ✓ Solo logs informativos o warnings menores

### 7. Pruebas de Visualización

#### Test 7.1: Ver reporte desde el dashboard del cliente
**Pasos**:
1. Hacer login como cliente (no admin)
2. Ir a Dashboard > Reportes
3. Click en un reporte con imágenes

**Resultado Esperado**:
- ✓ Las imágenes se cargan correctamente
- ✓ Se pueden ver las imágenes en la galería

#### Test 7.2: Ver reporte desde el panel de admin
**Pasos**:
1. Hacer login como admin
2. Ir a "Reportes del Jardín"
3. Click en "Ver" en cualquier reporte

**Resultado Esperado**:
- ✓ Todas las imágenes se muestran correctamente
- ✓ Se puede hacer click para ver en grande

---

## Logs a Verificar

Si algo falla, revisar los logs en:
- `storage/logs/laravel.log`

Buscar líneas que contengan:
- `Error uploading garden report image:`
- `Error creating garden report:`
- `Error updating garden report:`

---

## Comandos Útiles para Depuración

```bash
# Ver logs en tiempo real
php artisan tail

# Limpiar caché si algo no funciona
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Verificar rutas
php artisan route:list --name=garden-reports

# Verificar permisos del directorio storage
# (En Windows, verificar que el usuario tenga permisos de escritura)
```

---

## Contacto de Soporte

Si encuentras algún problema que no está en esta lista:
1. Captura de pantalla del error
2. Mensaje de error completo de la consola
3. Pasos exactos para reproducir el error
4. Revisar el archivo `storage/logs/laravel.log`
