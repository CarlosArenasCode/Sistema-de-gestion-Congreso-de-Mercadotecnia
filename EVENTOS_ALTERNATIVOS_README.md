# ✅ Sistema de Eventos Alternativos Implementado

## 🎯 Funcionalidad Implementada

El sistema ahora **sugiere automáticamente eventos alternativos** cuando un usuario intenta inscribirse a un evento que ya no tiene cupo disponible.

---

## 🔧 Archivos Modificados/Creados

### 1. **`php/sugerir_eventos_alternativos.php`** ✨ NUEVO
Endpoint independiente para buscar eventos alternativos.

**Criterios de búsqueda:**
- ✅ Mismo tipo de evento (conferencia/taller)
- ✅ Con cupo disponible
- ✅ Fechas futuras
- ✅ Prioridad: mismo ponente primero
- ✅ Ordenados por fecha más cercana
- ✅ Límite: 5 sugerencias máximo

**Uso:**
```
GET /php/sugerir_eventos_alternativos.php?id_evento=123
```

**Respuesta:**
```json
{
  "success": true,
  "evento_original": {
    "id_evento": 123,
    "nombre_evento": "Workshop de Marketing Digital",
    "tipo_evento": "taller"
  },
  "eventos_alternativos": [
    {
      "id_evento": 125,
      "nombre_evento": "Taller de SEO Avanzado",
      "fecha_inicio": "2025-11-28",
      "hora_inicio": "10:00",
      "lugar": "Aula 301",
      "ponente": "Ing. Carlos Ruiz",
      "cupos_disponibles": 15,
      "mismo_ponente": true
    }
  ],
  "total": 1
}
```

---

### 2. **`php/inscribir_evento.php`** 🔄 MODIFICADO

**Cambio principal:** Cuando detecta cupo lleno, ahora:
1. Busca hasta 3 eventos alternativos
2. Los incluye en la respuesta de error
3. Marca con `error_code: 'CUPO_LLENO'`

**Antes:**
```json
{
  "success": false,
  "message": "El cupo para este evento está lleno."
}
```

**Ahora:**
```json
{
  "success": false,
  "error_code": "CUPO_LLENO",
  "message": "El cupo para este evento está lleno.",
  "eventos_alternativos": [
    {
      "id_evento": 125,
      "nombre_evento": "Taller de SEO",
      "fecha_inicio": "2025-11-28",
      "hora_inicio": "10:00",
      "cupos_disponibles": 15
    }
  ],
  "tiene_alternativas": true
}
```

---

### 3. **`js/inscribirse_eventos.js`** 🔄 MODIFICADO

**Nuevas funciones agregadas:**

#### `mostrarEventosAlternativos(data)`
- Recibe datos de eventos alternativos
- Crea modal visual atractivo
- Muestra detalles de cada alternativa

#### `mostrarModalAlternativas(mensaje, eventos)`
- Genera modal HTML dinámico
- Botones de inscripción directa
- Diseño responsive y profesional

**Características del modal:**
- 🎨 Diseño moderno con colores intuitivos
- 📋 Información completa de cada evento
- ✅ Botón de inscripción inmediata
- 🚪 Botón de cerrar
- 📱 Scroll automático si hay muchos eventos

---

## 🎨 Experiencia de Usuario

### Flujo Completo:

```
1. Usuario intenta inscribirse a "Workshop Marketing Digital"
           ↓
2. Sistema detecta: CUPO LLENO (30/30)
           ↓
3. Sistema busca eventos alternativos:
   - Mismo tipo: taller
   - Con cupo disponible
   - Fechas futuras
   - Mismo ponente (prioridad)
           ↓
4. Muestra modal visual con 3 alternativas:
   ┌──────────────────────────────────────┐
   │  🚫 Evento Lleno                     │
   │                                      │
   │  ✨ Eventos Alternativos:            │
   │                                      │
   │  1. Taller SEO Avanzado              │
   │     📅 28 Nov 2025                   │
   │     🕐 10:00                         │
   │     📍 Aula 301                      │
   │     🎤 Ing. Carlos Ruiz              │
   │     👥 15 cupos disponibles          │
   │     [✅ Inscribirse]                 │
   │                                      │
   │  2. Workshop de Google Ads           │
   │     📅 30 Nov 2025                   │
   │     ...                              │
   │     [✅ Inscribirse]                 │
   │                                      │
   │  [Cerrar]                            │
   └──────────────────────────────────────┘
           ↓
5. Usuario hace clic en "Inscribirse"
           ↓
6. Inscripción automática al evento alternativo
           ↓
7. Confirmación de inscripción exitosa
```

---

## 🧪 Cómo Probar la Funcionalidad

### Opción 1: Llenar un evento manualmente

```powershell
# 1. Conectarse a Oracle
docker exec -it congreso_oracle_db bash -c "echo \"SELECT id_evento, nombre_evento, tipo_evento, cupo_actual, cupo_maximo FROM eventos ORDER BY fecha_inicio;\" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"

# 2. Elegir un evento y llenar su cupo (ejemplo: evento ID 1)
docker exec -it congreso_oracle_db bash -c "echo \"UPDATE eventos SET cupo_actual = cupo_maximo WHERE id_evento = 1; COMMIT;\" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"

# 3. Verificar que otro evento del mismo tipo tenga cupo
docker exec -it congreso_oracle_db bash -c "echo \"SELECT id_evento, nombre_evento, tipo_evento, cupo_actual, cupo_maximo FROM eventos WHERE tipo_evento = (SELECT tipo_evento FROM eventos WHERE id_evento = 1) AND cupo_actual < cupo_maximo;\" | sqlplus -S congreso_user/congreso_pass@FREEPDB1"
```

### Opción 2: Crear eventos de prueba

```sql
-- Evento que estará lleno
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'Taller Marketing LLENO',
    'Este taller está completo',
    TO_DATE('2025-12-01', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-01', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-01 12:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 101',
    'Dr. Pérez',
    20,  -- cupo_maximo
    20,  -- cupo_actual (LLENO)
    1,
    'taller',
    2.0
);

-- Evento alternativo 1
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'Taller Marketing Avanzado',
    'Alternativa disponible',
    TO_DATE('2025-12-02', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-02 14:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-02', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-02 16:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 202',
    'Dr. Pérez',  -- Mismo ponente (mayor prioridad)
    25,
    5,  -- Cupo disponible
    1,
    'taller',
    2.0
);

-- Evento alternativo 2
INSERT INTO eventos (
    nombre_evento, descripcion, fecha_inicio, hora_inicio, 
    fecha_fin, hora_fin, lugar, ponente, cupo_maximo, cupo_actual,
    genera_constancia, tipo_evento, horas_para_constancia
) VALUES (
    'Taller Digital Marketing',
    'Otra alternativa',
    TO_DATE('2025-12-03', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-03 09:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    TO_DATE('2025-12-03', 'YYYY-MM-DD'),
    TO_TIMESTAMP('2025-12-03 11:00:00', 'YYYY-MM-DD HH24:MI:SS'),
    'Aula 303',
    'Ing. López',
    30,
    10,
    1,
    'taller',
    2.0
);

COMMIT;
```

### Opción 3: Probar desde la interfaz web

1. **Abrir:** http://localhost:8081/Front-end/horario.html
2. **Buscar** un evento que muestre "Cupo Lleno"
3. **Hacer clic** en el botón (debería estar deshabilitado)
4. **O forzar inscripción** vía consola de desarrollador:

```javascript
// En la consola del navegador
handleInscriptionAction(ID_DEL_EVENTO_LLENO, 'inscribir')
```

---

## 🎯 Criterios de Búsqueda de Alternativas

El sistema busca eventos que cumplan **TODOS** estos criterios:

| Criterio | Descripción | Peso |
|----------|-------------|------|
| **Mismo tipo** | tipo_evento igual (taller/conferencia) | ⚡ Obligatorio |
| **Cupo disponible** | cupo_actual < cupo_maximo | ⚡ Obligatorio |
| **Fecha futura** | fecha_inicio >= HOY | ⚡ Obligatorio |
| **Mismo ponente** | Prioridad 1 si coincide | 🔥 Alta prioridad |
| **Fecha cercana** | Ordenado por proximidad | 📅 Media prioridad |

---

## 📊 Ejemplo de Respuesta Real

### Evento Lleno SIN Alternativas:
```json
{
  "success": false,
  "error_code": "CUPO_LLENO",
  "message": "El cupo para este evento está lleno.",
  "eventos_alternativos": [],
  "tiene_alternativas": false
}
```
**Resultado:** Mensaje simple sin modal.

---

### Evento Lleno CON Alternativas:
```json
{
  "success": false,
  "error_code": "CUPO_LLENO",
  "message": "El cupo para este evento está lleno.",
  "eventos_alternativos": [
    {
      "id_evento": 25,
      "nombre_evento": "Workshop SEO 2025",
      "tipo_evento": "taller",
      "ponente": "Dr. Pérez",
      "fecha_inicio": "2025-12-05",
      "hora_inicio": "14:00",
      "lugar": "Auditorio B",
      "cupo_maximo": 30,
      "cupo_actual": 12,
      "cupos_disponibles": 18
    },
    {
      "id_evento": 28,
      "nombre_evento": "Taller Google Analytics",
      "tipo_evento": "taller",
      "ponente": "Ing. Martínez",
      "fecha_inicio": "2025-12-08",
      "hora_inicio": "10:00",
      "lugar": "Lab. Cómputo 3",
      "cupo_maximo": 25,
      "cupo_actual": 8,
      "cupos_disponibles": 17
    }
  ],
  "tiene_alternativas": true
}
```
**Resultado:** Modal visual con 2 opciones de inscripción directa.

---

## ✨ Mejoras Implementadas

### Ventajas del Sistema:

1. **✅ UX Mejorada:**
   - Usuario no se queda sin opciones
   - Inscripción inmediata desde el modal
   - Información visual clara

2. **✅ Inteligencia de Búsqueda:**
   - Prioriza mismo ponente
   - Fecha más cercana posible
   - Solo eventos con cupo real

3. **✅ Performance:**
   - Query optimizado con FETCH FIRST
   - Solo 3 alternativas en inscripción
   - Endpoint independiente disponible

4. **✅ Flexibilidad:**
   - Funciona para talleres y conferencias
   - Se adapta si no hay alternativas
   - Compatible con sistema existente

---

## 🔍 Logs de Depuración

Si necesitas depurar, puedes verificar:

```javascript
// En la consola del navegador (Front-end/horario.html)
// Activar logs
console.log('Testing eventos alternativos...');

// Interceptar respuesta
fetch('../php/inscribir_evento.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_evento: ID_EVENTO_LLENO })
})
.then(r => r.json())
.then(d => console.log('Respuesta:', d));
```

---

## 📝 Notas Importantes

1. **Límite de sugerencias:**
   - Inscripción: 3 eventos máximo
   - Endpoint directo: 5 eventos máximo

2. **Compatibilidad:**
   - ✅ Compatible con validación de usuarios verificados
   - ✅ Compatible con control de cupos
   - ✅ Compatible con sistema de inscripciones existente

3. **No afecta:**
   - Eventos con cupo disponible funcionan igual
   - Cancelaciones siguen funcionando normal
   - Dashboard y reportes no cambian

---

## 🎉 Resultado Final

El sistema ahora cumple con el requerimiento:

> ✅ **"Implementar una funcionalidad que verifique la disponibilidad de cupos para los eventos. Si un evento está lleno, el sistema debe ofrecer eventos alternativos al usuario."**

**Estado:** ✅ **COMPLETAMENTE IMPLEMENTADO**

---

**Última actualización:** 26 de noviembre de 2025
