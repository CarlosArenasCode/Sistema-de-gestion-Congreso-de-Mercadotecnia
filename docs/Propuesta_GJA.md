# Propuesta de Proyecto: "Gestión Jurídica Académica" (GJA)

## 1. Título
Sistema de Gestión Académica y de Evidencias para la Materia de Derecho — "Gestión Jurídica Académica" (GJA)

## 2. Resumen ejecutivo
Adaptación del sistema base (Sistema de gestión - Congreso de Mercadotecnia) para crear una plataforma dirigida a la materia de Derecho que permita gestionar asistencias a actividades formativas (clases, seminarios, prácticas jurídicas y juicios simulados), emitir constancias y evidencias digitales, almacenar y vincular contenidos jurídicos (legislación, jurisprudencia, doctrina) y garantizar el cumplimiento del marco normativo federal y del Estado de Aguascalientes en materia educativa, protección de datos personales y validez de constancias digitales.

## 3. Justificación
La formación del estudiante de Derecho exige trazabilidad de evidencias prácticas y documentales. Una plataforma que gestione asistencias, reúna evidencias, emita constancias verificables y conserve un repositorio jurídico facilita la evaluación, transparencia y probatoria académica. Además, exige cumplimiento de la Constitución mexicana y leyes en materia de protección de datos y validez de documentos electrónicos.

## 4. Objetivos
- Objetivo general: Adaptar el sistema existente para que funcione como plataforma integral de gestión académica y de evidencias para la materia de Derecho, cumpliendo la normativa aplicable.
- Objetivos específicos:
  - Gestionar inscripción, control de asistencia (QR y registro manual) y expedición de constancias digitales con metadatos jurídicos.
  - Integrar un repositorio de legislación, jurisprudencia y material didáctico categorizado.
  - Implementar módulos de evaluación de prácticas con rúbricas y firmas de responsables.
  - Implementar aviso de privacidad, gestión de consentimiento y derechos ARCO.
  - Proveer un mecanismo de verificación pública de constancias (endpoint con validación de hash/UUID).

## 5. Alcance
- Usuarios: alumnos, profesores, coordinadores y administradores.
- Funcionalidades principales: gestión de usuarios/roles, inscripción a actividades, control de asistencia vía QR, generación de constancias PDF con QR/verificación, repositorio jurídico, módulo de evaluación y auditoría, envío de notificaciones por correo seguro.

## 6. Mapeo rápido del repositorio actual (aprovechamiento)
- `Proyecto_conectado/Front-end/`: páginas HTML para dashboards, login y administración.
- `js/`, `js_admin/`: scripts para QR, certificados, dashboards.
- `php/`: backend en PHP con `conexion.php`, controladores de inscripciones/asistencia, `generar_constancia.php`, `fpdf/` y `PHPMailer.php`.
- `sql/congreso_db.sql`: esquema base de BD.

Estos módulos proporcionan la base para adaptar el sistema a requisitos jurídicos (añadiendo metadatos, endpoints de verificación y cambios en plantillas de constancia).

## 7. Marco jurídico: leyes y aplicación práctica (resumen)
A continuación se listan las disposiciones federales y estatales más relevantes, con un resumen de su alcance y la forma en que afectan al sistema. En la sección de referencias se incluyen enlaces a las fuentes oficiales. Si prefieres, puedo insertar a continuación el texto literal de los artículos citados.

### 7.1 Constitución Política de los Estados Unidos Mexicanos
- Art. 3° (Derecho a la educación): establece la obligatoriedad y los fines de la educación; obliga a que certificaciones y constancias académicas cumplan con criterios institucionales y de validez administrativa. Aplicación: el sistema debe conservar evidencias y generar constancias con metadatos que permitan acreditar cumplimiento de horas y contenidos.
- Art. 6° (Derecho de acceso a la información): garantiza el derecho a la información; aplicable para permitir acceso a documentos públicos y material didáctico, cuando proceda.
- Art. 7° (Libertad de expresión): relevante marginalmente para contenidos, pero no limita la gestión académica.
- Art. 16° (Protección de la privacidad y prohibición de injerencias arbitrarias): impone límites al tratamiento de datos personales y exige que los mecanismos de recolección y conservación cuenten con fundamentos legales y garantías.

### 7.2 Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)
Resumen de obligaciones clave:
- Principio de licitud, consentimiento y finalidad: los datos personales deben recabarse para fines determinados y con el consentimiento del titular.
- Aviso de privacidad: el responsable debe proporcionar información clara sobre uso y tratamiento.
- Derechos ARCO (Acceso, Rectificación, Cancelación y Oposición) y mecanismos para atenderlos.
- Medidas de seguridad técnicas y administrativas proporcionales.
Aplicación: incorporar un aviso de privacidad desde el registro, almacenar consentimientos, encriptar campos sensibles y proveer endpoints para solicitudes ARCO.

### 7.3 Validez de documentos electrónicos y firma digital
- Normativa federal relacionada con firma electrónica y conservación de documentos (por ejemplo la Ley de Firma Electrónica y disposiciones del Código de Comercio y Código Civil Federal sobre documentos electrónicos) permite que documentos digitales sean prueba si cumplen requisitos de integridad y autenticidad.
Aplicación: las constancias deberán generarse con metadatos (UUID, marca temporal, hash) e incluir un mecanismo de verificación pública (URL y/o código QR) y, si se requiere valor probatorio adicional, integrarse con un proveedor de firma electrónica o implementar sellos criptográficos.

### 7.4 Constitución y normativa del Estado de Aguascalientes
- Constitución Política del Estado de Aguascalientes y leyes locales en materia educativa y de protección de datos: contienen disposiciones complementarias sobre educación y deberes de las autoridades locales.
Aplicación: adaptar plantillas de constancia para cumplir requisitos estatales (pie de firma, identificación de la institución, sellos) y revisar si existe una ley estatal de protección de datos con obligaciones adicionales.

### 7.5 Buenas prácticas y normas complementarias
- Recomendaciones: emitir avisos de privacidad visibles, registrar y conservar logs de emisión, emplear HTTPS y cifrado en reposo para datos sensibles, y preparar un protocolo de retención y eliminación de datos.

> Nota: los puntos anteriores son resúmenes prácticos. Puedo pegar los textos literales de los artículos constitucionales y los artículos relevantes de la LFPDPPP y la Constitución del Estado de Aguascalientes directamente en este documento (con citas y enlaces oficiales). Dime si quieres que los incluya textualmente en el cuerpo del documento.

## 8. Metodología y fases (resumido)
1. Análisis y requisitos (1–2 semanas).
2. Diseño (1 semana).
3. Implementación (3–6 semanas).
4. Pruebas y validación legal (1–2 semanas).
5. Piloto y capacitación (1–2 semanas).
6. Ajustes y entrega (1 semana).

## 9. Entregables técnicos
- Código adaptado y documentado.
- Tablas nuevas: `constancias`, `evidencias`, `metadatos_constancia`.
- Endpoints: `generar_constancia.php` (adaptado), `verificar_constancia.php`, `arco_request.php`.
- Vistas: `repositorio_juridico.html`, `evaluacion_practicas.html`, `ver_constancia.html`.
- Plantillas de constancias con QR y hash.
- Documentación legal: aviso de privacidad y política de retención.

## 10. Plan de cumplimiento legal (acciones concretas)
- Integrar avisos de privacidad en la UI y registrar consentimientos en BD.
- Encriptar campos sensibles (curp, correo, teléfono si procede).
- Generar firma/verificación en constancias: UUID + hash SHA-256 impreso como QR y endpoint público de verificación.
- Mantener logs inmutables (fecha, usuario emisor, actividad).
- Implementar procedimientos para solicitudes ARCO.

## 11. Cronograma sugerido (12 semanas) y recursos
Ver sección del resumen general.

## 12. Siguientes pasos sugeridos
1. Confirmar que quieres que incluya los textos literales y referencias exactas de los artículos federales y estatales (yo puedo pegarlos en el documento). 
2. Indicar si requieres que el documento se entregue también como PDF final (puedo generar HTML listo para imprimir; la conversión a PDF desde el navegador es simple). 

---

*Documento generado automáticamente a partir del análisis del repositorio en `Proyecto_conectado/`. Para obtener textos literales de artículos y leyes incluidas, responde confirmando y en un siguiente paso los incorporaré con citas oficiales.*
