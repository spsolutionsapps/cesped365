# Base de datos – qué script usar y cuándo

## Por qué “se borra” el admin al subir cosas

El admin (y a veces los planes) desaparecen **solo si en algún momento se ejecuta** el script que **borra todas las tablas** y las vuelve a crear vacías.

Ese script es: **`database_create_all_tables_phpmyadmin.sql`**

- Tiene **`DROP TABLE IF EXISTS ...`** para cada tabla.
- Luego hace **`CREATE TABLE ...`**.
- Resultado: la base queda con la estructura correcta pero **sin datos** (ni admin, ni usuarios, ni reportes, ni planes).

No es “subir código” lo que borra el admin: es **ejecutar ese SQL en phpMyAdmin** (o que algún paso de deploy lo ejecute) sobre la base de producción.

---

## Cómo evitar borrar todo a futuro

### 1. No uses el script completo en producción

- **`database_create_all_tables_phpmyadmin.sql`** → usarlo **solo** en una base **nueva/vacía** (primera instalación o pruebas).
- **En producción**, si ya tienes datos, **no lo ejecutes**. Si lo ejecutas, perderás todo.

### 2. Si solo agregaste tablas o columnas nuevas

- Para **tablas nuevas** (ej. `manual_gains`): usa el script que solo crea esa tabla, por ejemplo **`create_manual_gains_table.sql`** o **`create_blocked_days_table.sql`**.
- Para **nuevas columnas**: mejor usar las migraciones de CodeIgniter (`php spark migrate`) en el servidor, o un script SQL que solo haga `ALTER TABLE ... ADD COLUMN ...` y **no** haga `DROP TABLE`.

### 3. Después de un “reset” completo (solo si lo hiciste a propósito)

Si en algún momento ejecutaste el script que borra todo (en una base nueva o porque decidiste resetear):

1. **Admin:** abrir en el navegador  
   `https://tudominio.com/.../public/seed_admin.php?ejecutar=1`  
   Luego borrar el archivo `seed_admin.php` del servidor.

2. **Planes:** abrir  
   `https://tudominio.com/.../public/seed_plans_produccion.php?ejecutar=1`  
   Luego borrar el archivo del servidor.

---

## Resumen rápido

| Situación | Qué hacer |
|----------|-----------|
| Primera vez, base vacía | Ejecutar `database_create_all_tables_phpmyadmin.sql` y después los seeds (admin + planes). |
| Producción con datos | No ejecutar el script completo. Usar solo scripts de una tabla (ej. `create_manual_gains_table.sql`) o migraciones. |
| “Se borró el admin” | Alguien ejecutó el script completo. Volver a crear admin con `seed_admin.php?ejecutar=1`. |
| Miedo a borrar toda la base | No ejecutar nunca `database_create_all_tables_phpmyadmin.sql` en la base de producción; dejar ese script solo para bases nuevas o pruebas. |

El código que subes (PHP, Svelte, etc.) **no borra** la base de datos. Solo los scripts SQL que ejecutes (o que ejecute un deploy) pueden hacer DROP/CREATE. Con esto puedes subir “muchas cosas” con tranquilidad, siempre que no ejecutes el script completo en la base de producción.
