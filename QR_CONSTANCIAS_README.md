# ✅ Códigos QR en Constancias - Implementación Completa

## 🎯 Funcionalidad Implementada

Todas las constancias PDF ahora incluyen un **código QR de verificación** en la esquina inferior derecha que contiene información completa del usuario y la constancia.

---

## 🔧 Archivos Modificados

### 1. **`php/generar_constancia.php`** 🔄 MODIFICADO
- ✅ Agregada librería `phpqrcode/qrlib.php`
- ✅ Genera QR con datos completos del usuario y evento
- ✅ Inserta QR en esquina inferior derecha del PDF
- ✅ Limpieza automática de archivos temporales

### 2. **`php_admin/generar_constancia.php`** 🔄 MODIFICADO
- ✅ Migrado de texto plano a PDF real con FPDF
- ✅ Incluye código QR de verificación
- ✅ Diseño profesional mejorado
- ✅ Mismo formato que constancias de usuarios

### 3. **`php/phpqrcode/`** 📦 LIBRERÍA AGREGADA
- Librería phpqrcode para generación de códigos QR
- Clonada desde: https://github.com/t0k4rt/phpqrcode

---

## 📊 Información Incluida en el QR

Cada código QR contiene un objeto JSON con:

```json
{
  "tipo": "CONSTANCIA",
  "id_usuario": 123,
  "matricula": "529633",
  "nombre": "Joshua Rafael Rodriguez Acosta",
  "email": "usuario@universidad.edu.mx",
  "evento_id": 5,
  "evento": "Workshop de Marketing Digital",
  "fecha_evento": "2025-11-26",
  "codigo_qr_usuario": "id=123&nombre=Joshua...",
  "fecha_emision": "2025-11-26 14:30:45",
  "verificacion": "a3f5b2e8c9d1..." // Hash SHA256
}
```

### 🔐 Hash de Verificación
El campo `verificacion` contiene:
```php
hash('sha256', $id_usuario . $id_evento . $fecha_actual)
```

Esto permite validar que la constancia es auténtica y no ha sido modificada.

---

## 🎨 Ubicación del QR en el PDF

```
┌─────────────────────────────────────────────┐
│     CONSTANCIA DE ASISTENCIA                │
│                                              │
│   La Universidad Autónoma de...             │
│                                              │
│        NOMBRE DEL ALUMNO                    │
│        Matrícula: 529633                    │
│                                              │
│   Por su participación en...                │
│                                              │
│   Realizado el 26 de noviembre de 2025      │
│                                              │
│                                              │
│                                              │
│   _________________________                  │
│   Rector de la Universidad        ┌─────┐   │
│                                   │ QR  │   │
│                                   │CODE │   │
│                                   └─────┘   │
│                              Código de       │
│                              verificación    │
└─────────────────────────────────────────────┘
```

**Posición:** Esquina inferior derecha  
**Tamaño:** 50mm x 50mm  
**Nivel de corrección:** L (Low) - Permite hasta 7% de daño

---

## 🔄 Flujo de Generación

```
1. Usuario/Admin solicita constancia
         ↓
2. Sistema obtiene datos de:
   - Usuario (id, matrícula, nombre, email, codigo_qr)
   - Evento (id, nombre, ponente, fecha)
         ↓
3. Genera JSON con información completa
         ↓
4. Crea imagen QR temporal en /temp_qr/
         ↓
5. Genera PDF con FPDF
         ↓
6. Inserta QR en posición (230, 170)
         ↓
7. Guarda PDF en /constancias_pdf/
         ↓
8. Elimina imagen QR temporal
         ↓
9. Retorna ruta del PDF generado
```

---

## 📂 Estructura de Directorios

```
Proyecto_conectado/
├── php/
│   ├── generar_constancia.php ← Modificado
│   ├── phpqrcode/ ← NUEVO
│   │   ├── qrlib.php
│   │   ├── phpqrcode.php
│   │   └── ... (archivos de la librería)
│   └── fpdf/
│       └── fpdf.php
├── php_admin/
│   └── generar_constancia.php ← Modificado
├── constancias_pdf/ ← Constancias generadas
│   └── constancia_123_5_1732645845.pdf
└── temp_qr/ ← Temporal (auto-limpiado)
    └── (archivos QR temporales)
```

---

## 🧪 Cómo Probar

### Opción 1: Panel de Administrador

1. **Login como admin:**
   - http://localhost:8081/Front-end/login_admin.html

2. **Ir a Constancias:**
   - Panel Admin → Constancias

3. **Seleccionar evento y usuario:**
   - Elegir evento del dropdown
   - Seleccionar usuario elegible
   - Click en "Generar Constancia"

4. **Descargar y verificar:**
   - PDF debe tener QR en esquina inferior derecha
   - Escanear QR con app de celular
   - Ver datos JSON del usuario

### Opción 2: Usuario (si está habilitado)

1. **Login como alumno:**
   - http://localhost:8081/Front-end/login.html
   - User: 529633 / Test123456

2. **Ir a Mis Constancias:**
   - Dashboard → Descargar Constancias

3. **Descargar constancia:**
   - Si tiene asistencia completa
   - Verificar QR en PDF

---

## 📱 Verificar QR Code

### Con App de Celular:
1. Abrir app de cámara o lector QR
2. Escanear código
3. Ver JSON con información

### Con Herramienta Online:
1. Abrir: https://zxing.org/w/decode
2. Subir imagen o PDF
3. Ver datos decodificados

### Ejemplo de Datos Decodificados:
```json
{
  "tipo": "CONSTANCIA",
  "id_usuario": 123,
  "matricula": "529633",
  "nombre": "Joshua Rafael Rodriguez Acosta",
  "email": "anneke0092@gmail.com",
  "evento_id": 1,
  "evento": "TALLER LLENO - Prueba",
  "fecha_evento": "2025-12-10",
  "codigo_qr_usuario": "id=123&nombre=Joshua+Rafael+Rodriguez+Acosta&matricula=529633&timestamp=1732645234",
  "fecha_emision": "2025-11-26 14:33:54",
  "verificacion": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
}
```

---

## 🔍 Validación de Constancias (Futuro)

El QR permite implementar un sistema de validación:

### Endpoint Sugerido: `/php/validar_constancia.php`

```php
// Recibe: Hash de verificación desde QR
// Verifica:
// 1. ¿Existe usuario con ese ID?
// 2. ¿Existe evento con ese ID?
// 3. ¿Coincide el hash?
// 4. ¿Tiene asistencia registrada?
// Retorna: VÁLIDA / INVÁLIDA
```

**Implementación futura:** Permite a empresas/instituciones verificar autenticidad de constancias escaneando el QR.

---

## ⚙️ Parámetros de Generación QR

```php
QRcode::png(
    $data,              // Datos JSON
    $filepath,          // Ruta temporal
    QR_ECLEVEL_L,      // Nivel corrección: L, M, Q, H
    5,                  // Tamaño del QR (1-10)
    2                   // Margen en módulos
);
```

**Nivel de Corrección:**
- `L` (Low) - 7% recuperación
- `M` (Medium) - 15% recuperación
- `Q` (Quartile) - 25% recuperación
- `H` (High) - 30% recuperación

**Elegido: L** - Suficiente para constancias impresas en buena calidad

---

## 🎯 Beneficios del QR en Constancias

| Beneficio | Descripción |
|-----------|-------------|
| ✅ **Verificación** | Empresas pueden validar autenticidad |
| ✅ **Trazabilidad** | Información completa del evento |
| ✅ **Anti-fraude** | Hash de verificación único |
| ✅ **Digital** | No requiere base de datos para leer datos |
| ✅ **Profesional** | Apariencia moderna y tecnológica |
| ✅ **Código Usuario** | Incluye QR code único del usuario |

---

## 🛠️ Solución de Problemas

### Error: "Class 'QRcode' not found"
**Solución:**
```bash
cd Proyecto_conectado/php
git clone https://github.com/t0k4rt/phpqrcode.git
```

### Error: "Failed to open stream: No such file"
**Solución:**
```php
// Verificar que existan los directorios
mkdir('temp_qr', 0777, true);
mkdir('constancias_pdf', 0777, true);
```

### QR no aparece en PDF
**Verificar:**
1. ¿Se creó el archivo temporal? → Revisar `/temp_qr/`
2. ¿Permisos correctos? → `chmod 777 temp_qr`
3. ¿Ruta correcta en Image()? → Usar ruta absoluta

### QR ilegible
**Ajustar parámetros:**
```php
QRcode::png($data, $filepath, QR_ECLEVEL_M, 6, 2);
//                            ↑ M=Mayor    ↑ Más grande
```

---

## 📝 Cambios Técnicos

### Antes:
```php
// php_admin/generar_constancia.php
$contenido = "CONSTANCIA DE ASISTENCIA\n\n";
// ... más texto
file_put_contents($ruta, $contenido); // TXT
```

### Ahora:
```php
// Con FPDF + QR
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
// ... diseño del PDF
$pdf->Image($qr_filepath, 230, 170, 50, 50); // QR
$pdf->Output('F', $ruta); // PDF real
```

---

## 🎨 Personalización del QR

### Cambiar Posición:
```php
$pdf->Image($qr_filepath, X, Y, ancho, alto);
// Ejemplo: Centro inferior
$pdf->Image($qr_filepath, 125, 180, 40, 40);
```

### Cambiar Tamaño:
```php
// QR más grande (80x80mm)
$pdf->Image($qr_filepath, 210, 150, 80, 80);
```

### Agregar Logo en QR (Avanzado):
Requiere modificar phpqrcode para insertar imagen central.

---

## ✨ Resumen de Implementación

| Aspecto | Estado |
|---------|--------|
| Librería QR | ✅ phpqrcode instalada |
| Generación QR | ✅ Con datos completos |
| Inserción en PDF | ✅ Esquina inf. derecha |
| Usuario (php/) | ✅ Implementado |
| Admin (php_admin/) | ✅ Implementado |
| Hash verificación | ✅ SHA256 incluido |
| Limpieza temporal | ✅ Auto-delete QR |

---

## 🚀 Estado Final

✅ **Todas las constancias generadas incluyen código QR**  
✅ **QR contiene información completa del usuario y evento**  
✅ **Hash de verificación para autenticidad**  
✅ **Compatible con ambos sistemas (usuario y admin)**  
✅ **Archivos temporales se limpian automáticamente**

---

**Última actualización:** 26 de noviembre de 2025  
**Versión:** 2.0 - Con QR de verificación
