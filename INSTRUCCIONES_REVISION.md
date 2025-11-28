# 🚀 Instrucciones Rápidas - Revisión de Módulos

## ⚡ Inicio Rápido

### 1. Generar Datos de Prueba

```bash
# Ejecutar el seeder de datos de prueba
php artisan db:seed --class=TestDataSeeder
```

Esto creará:
- ✅ 6 usuarios (1 admin, 2 técnicos, 3 usuarios finales)
- ✅ 8 tickets con diferentes estados y prioridades
- ✅ Comentarios y actividades en los tickets
- ✅ Configuraciones de SLA
- ✅ Un ticket vencido para probar indicadores

### 2. Credenciales de Acceso

Todos los usuarios tienen la misma contraseña: **`password`**

| Tipo | Email | Nombre |
|------|-------|--------|
| **Admin** | admin@helptech.com | Administrador Principal |
| **Técnico** | tech1@helptech.com | Juan Técnico Gómez |
| **Técnico** | tech2@helptech.com | María Soporte Rodríguez |
| **Usuario** | usuario1@helptech.com | Pedro Usuario Pérez |
| **Usuario** | usuario2@helptech.com | Ana Usuario López |
| **Usuario** | usuario3@helptech.com | Carlos Usuario Ramírez |

### 3. URLs de Acceso

- **Login:** http://localhost:8000/login
- **Dashboard:** http://localhost:8000/dashboard
- **Tickets:** http://localhost:8000/tickets
- **Usuarios:** http://localhost:8000/users
- **Importar Usuarios:** http://localhost:8000/users-import
- **Configuración:** http://localhost:8000/settings

---

## 📋 Plan de Revisión por Módulos

### Módulo 1: Sistema de Tickets

**Login como:** `admin@helptech.com`

1. Ver listado de tickets
   - ✅ Debe mostrar 8 tickets
   - ✅ Verificar que se ven los estados con colores
   - ✅ Verificar badge de "Vencido" en tickets pasados de fecha

2. Filtrar tickets
   - ✅ Por estado: Nuevo, Abierto, En Progreso, etc.
   - ✅ Por prioridad: Baja, Normal, Alta, Urgente
   - ✅ Por categoría: Hardware, Software, Red, etc.
   - ✅ Tickets vencidos

3. Ver detalles de un ticket
   - ✅ Información completa
   - ✅ Historial de actividades
   - ✅ Comentarios públicos y privados

4. Crear nuevo ticket
   - ✅ Llenar formulario
   - ✅ Verificar que se genera número automático
   - ✅ Verificar que se calcula el SLA según prioridad

5. Asignar ticket
   - ✅ Asignar a un técnico
   - ✅ Verificar notificación (si está implementada)

6. Cambiar estado
   - ✅ Cambiar de Nuevo → Abierto
   - ✅ Cambiar de Abierto → En Progreso
   - ✅ Resolver ticket
   - ✅ Cerrar ticket

7. Agregar comentarios
   - ✅ Comentario público
   - ✅ Nota interna (privada)

### Módulo 2: Gestión de Usuarios

**Login como:** `admin@helptech.com`

1. Ver listado de usuarios
   - ✅ Debe mostrar 6 usuarios

2. Filtrar usuarios
   - ✅ Por tipo: Admin, Técnico, Usuario
   - ✅ Por empresa
   - ✅ Por estado (activo/inactivo)

3. Crear nuevo usuario
   - ✅ Llenar todos los campos
   - ✅ Asignar tipo correcto
   - ✅ Verificar validaciones

4. Editar usuario existente
   - ✅ Cambiar datos
   - ✅ Guardar cambios
   - ✅ Verificar que se actualizó

5. Desactivar/Activar usuario
   - ✅ Desactivar un usuario
   - ✅ Verificar que aparece como inactivo
   - ✅ Reactivar

### Módulo 3: Importación Masiva

**Login como:** `admin@helptech.com`

1. Descargar plantilla
   - ✅ Click en "Descargar Plantilla Excel"
   - ✅ Abrir archivo
   - ✅ Verificar que tiene ejemplos

2. Preparar datos
   - ✅ Agregar 3-5 usuarios de prueba
   - ✅ Incluir diferentes tipos (user, tech, admin)
   - ✅ Guardar como .xlsx

3. Importar archivo
   - ✅ Arrastrar archivo o seleccionar
   - ✅ Ver preview
   - ✅ Click en "Importar"
   - ✅ Verificar mensaje de éxito

4. Ver historial
   - ✅ Ver registro de importación
   - ✅ Verificar estadísticas (exitosos/errores)

5. Probar validaciones
   - ✅ Archivo sin email (debe fallar)
   - ✅ Email duplicado (debe actualizar)
   - ✅ Archivo muy grande >5MB (debe rechazar)

### Módulo 4: Configuración del Sistema

**Login como:** `admin@helptech.com`

1. Ver configuraciones actuales
   - ✅ Ver todas las configuraciones de SLA
   - ✅ Verificar valores por defecto

2. Modificar configuración
   - ✅ Cambiar "SLA Urgente" de 4 a 2 horas
   - ✅ Guardar cambios
   - ✅ Verificar mensaje de éxito

3. Verificar aplicación en tickets
   - ✅ Crear ticket con prioridad Urgente
   - ✅ Verificar que el due_date sea en 2 horas (no 4)

4. Cambiar prioridad de ticket existente
   - ✅ Editar un ticket
   - ✅ Cambiar prioridad de Normal a Urgente
   - ✅ Verificar que se recalcula el due_date

5. Restaurar valores por defecto
   - ✅ Click en "Restaurar valores"
   - ✅ Verificar que vuelven a los originales

### Módulo 5: Notificaciones

**Login como:** `tech1@helptech.com`

1. Ver notificaciones
   - ✅ Verificar campana en header
   - ✅ Ver contador de no leídas

2. Generar notificación
   - ✅ Login como admin
   - ✅ Asignar un ticket a tech1
   - ✅ Login como tech1
   - ✅ Verificar nueva notificación

3. Marcar como leída
   - ✅ Click en notificación
   - ✅ Verificar que desaparece contador

4. Eliminar notificación
   - ✅ Eliminar una notificación
   - ✅ Verificar que se eliminó

### Módulo 6: Dashboard

**Login con cada tipo de usuario:**

1. **Como Admin** (admin@helptech.com)
   - ✅ Ver estadísticas globales
   - ✅ Ver todos los tickets
   - ✅ Verificar contadores

2. **Como Técnico** (tech1@helptech.com)
   - ✅ Ver sus tickets asignados
   - ✅ Ver tickets creados por él
   - ✅ Verificar que NO ve todos los tickets

3. **Como Usuario** (usuario1@helptech.com)
   - ✅ Ver SOLO sus propios tickets
   - ✅ Verificar que NO ve tickets de otros

### Módulo 7: Permisos y Roles

**Objetivo:** Verificar que cada tipo de usuario solo puede hacer lo permitido

1. **Usuario Final** (usuario1@helptech.com)
   - ✅ Puede: Crear tickets, ver sus tickets, comentar
   - ❌ NO puede: Ver usuarios, asignar tickets, cambiar configs

2. **Técnico** (tech1@helptech.com)
   - ✅ Puede: Ver tickets asignados, asignar, resolver, cerrar
   - ❌ NO puede: Ver usuarios de otros, cambiar configs, importar

3. **Administrador** (admin@helptech.com)
   - ✅ Puede: TODO

---

## 🧹 Limpiar Datos de Prueba

Si quieres volver a empezar:

```bash
# Opción 1: Eliminar solo tickets
php artisan tinker
>>> App\Models\TicketComment::truncate();
>>> App\Models\TicketActivity::truncate();
>>> App\Models\Ticket::withTrashed()->forceDelete();

# Opción 2: Eliminar todo (usuarios + tickets)
php artisan migrate:fresh
php artisan db:seed --class=TestDataSeeder

# Opción 3: Solo regenerar tickets (mantener usuarios)
php artisan tinker
>>> App\Models\TicketComment::truncate();
>>> App\Models\TicketActivity::truncate();
>>> App\Models\Ticket::withTrashed()->forceDelete();
>>> exit
php artisan db:seed --class=TestDataSeeder
```

---

## 📊 Checklist de Revisión Rápida

### ✅ Funcionalidad Básica
- [ ] Login funciona
- [ ] Dashboard muestra datos
- [ ] Listado de tickets funciona
- [ ] Crear ticket funciona
- [ ] Filtros funcionan
- [ ] Ver detalles de ticket funciona

### ✅ Permisos
- [ ] Admin ve todo
- [ ] Técnico ve solo sus tickets
- [ ] Usuario ve solo sus tickets
- [ ] Menu cambia según rol

### ✅ UX/UI
- [ ] Diseño responsive
- [ ] Sin errores en consola
- [ ] Mensajes de éxito/error visibles
- [ ] Indicadores de carga funcionan
- [ ] Colores y badges correctos

### ✅ Performance
- [ ] Carga rápida de páginas
- [ ] Filtros responden bien
- [ ] Sin queries N+1 evidentes

---

## 🐛 Problemas Encontrados

### 🟢 Resueltos
- Error 500 por método validate() → Renombrado a preview()

### 🔴 Pendientes
(Se irá llenando durante la revisión)

---

## 💡 Mejoras Sugeridas

(Se irá llenando durante la revisión)

---

## 📝 Notas

- Todos los datos de prueba tienen fechas relativas (ayer, hace 2 días, etc.)
- El seeder usa `updateOrCreate` por lo que es seguro ejecutarlo múltiples veces
- Los tickets tienen diferentes estados para probar todos los casos
- Hay un ticket deliberadamente vencido para probar indicadores

---

**Última actualización:** 28/11/2025
**Creado por:** Claude Code
