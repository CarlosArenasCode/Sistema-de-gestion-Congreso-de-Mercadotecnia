<?php
/**
 * Script de Pruebas Completas del Sistema
 * Prueba todas las funcionalidades: CRUD de usuarios, eventos, inscripciones, asistencias
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php';

class SistemaTester {
    private $pdo;
    private $resultados = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "<h1>🧪 Pruebas del Sistema de Gestión - Congreso</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .test { margin: 15px 0; padding: 15px; border-radius: 5px; }
            .success { background: #d4edda; border-left: 4px solid #28a745; }
            .error { background: #f8d7da; border-left: 4px solid #dc3545; }
            .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
            h2 { color: #333; margin-top: 30px; }
            pre { background: #fff; padding: 10px; border-radius: 3px; overflow-x: auto; }
        </style>\n";
        
        // Fase 1: Conectividad
        $this->testConexionBD();
        
        // Fase 2: Estructura de Base de Datos
        $this->testEstructuraTablas();
        
        // Fase 3: CRUD de Usuarios
        $this->testCRUDUsuarios();
        
        // Fase 4: CRUD de Eventos
        $this->testCRUDEventos();
        
        // Fase 5: Inscripciones
        $this->testInscripciones();
        
        // Fase 6: Asistencias
        $this->testAsistencias();
        
        // Fase 7: Constancias
        $this->testConstancias();
        
        // Resumen Final
        $this->mostrarResumen();
    }
    
    private function testConexionBD() {
        echo "<h2>📡 Fase 1: Conectividad de Base de Datos</h2>\n";
        
        try {
            if ($this->pdo) {
                $this->registrarExito("Conexión a base de datos establecida correctamente");
                
                // Verificar tipo de base de datos
                $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
                $this->registrarInfo("Driver de base de datos: " . $driver);
                
                // Test de consulta simple
                $stmt = $this->pdo->query("SELECT 1 FROM DUAL");
                if ($stmt) {
                    $this->registrarExito("Consulta de prueba ejecutada correctamente");
                }
            } else {
                $this->registrarError("No se pudo establecer conexión con la base de datos");
            }
        } catch (Exception $e) {
            $this->registrarError("Error de conexión: " . $e->getMessage());
        }
    }
    
    private function testEstructuraTablas() {
        echo "<h2>🗄️ Fase 2: Estructura de Tablas</h2>\n";
        
        $tablasRequeridas = [
            'USUARIOS',
            'ADMINISTRADORES',
            'EVENTOS',
            'INSCRIPCIONES',
            'ASISTENCIA',
            'CONSTANCIAS',
            'JUSTIFICACIONES'
        ];
        
        foreach ($tablasRequeridas as $tabla) {
            try {
                $stmt = $this->conn->query("SELECT COUNT(*) as total FROM $tabla");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->registrarExito("Tabla $tabla existe - Registros: " . $result['TOTAL']);
            } catch (Exception $e) {
                $this->registrarError("Tabla $tabla no existe o no es accesible: " . $e->getMessage());
            }
        }
    }
    
    private function testCRUDUsuarios() {
        echo "<h2>👤 Fase 3: CRUD de Usuarios</h2>\n";
        
        // CREATE - Crear usuario de prueba
        try {
            $email = 'test_' . time() . '@prueba.com';
            $password = password_hash('Test123!', PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO USUARIOS (NOMBRE_COMPLETO, EMAIL, PASSWORD_HASH, MATRICULA, SEMESTRE, TELEFONO, ROL) 
                    VALUES (:nombre, :email, :password, :matricula, :semestre, :telefono, :rol)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nombre' => 'Usuario Test',
                ':email' => $email,
                ':password' => $password,
                ':matricula' => 'TEST' . time(),
                ':semestre' => 5,
                ':telefono' => '4491234567',
                ':rol' => 'alumno'
            ]);
            
            $userId = $this->conn->lastInsertId();
            $this->registrarExito("✅ CREATE: Usuario creado con ID: $userId");
            
            // READ - Leer usuario
            $stmt = $this->conn->prepare("SELECT * FROM USUARIOS WHERE ID_USUARIO = :id");
            $stmt->execute([':id' => $userId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $this->registrarExito("✅ READ: Usuario leído correctamente - " . $usuario['NOMBRE_COMPLETO']);
            }
            
            // UPDATE - Actualizar usuario
            $stmt = $this->conn->prepare("UPDATE USUARIOS SET SEMESTRE = :semestre WHERE ID_USUARIO = :id");
            $stmt->execute([':semestre' => 6, ':id' => $userId]);
            $this->registrarExito("✅ UPDATE: Usuario actualizado (semestre cambiado a 6)");
            
            // DELETE - Eliminar usuario
            $stmt = $this->conn->prepare("DELETE FROM USUARIOS WHERE ID_USUARIO = :id");
            $stmt->execute([':id' => $userId]);
            $this->registrarExito("✅ DELETE: Usuario eliminado correctamente");
            
        } catch (Exception $e) {
            $this->registrarError("Error en CRUD de usuarios: " . $e->getMessage());
        }
    }
    
    private function testCRUDEventos() {
        echo "<h2>📅 Fase 4: CRUD de Eventos</h2>\n";
        
        try {
            // CREATE - Crear evento
            $sql = "INSERT INTO EVENTOS (NOMBRE_EVENTO, DESCRIPCION, FECHA_INICIO, HORA_INICIO, FECHA_FIN, HORA_FIN, LUGAR, PONENTE, CUPO_MAXIMO, TIPO_EVENTO) 
                    VALUES (:nombre, :desc, :fecha_ini, :hora_ini, :fecha_fin, :hora_fin, :lugar, :ponente, :cupo, :tipo)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nombre' => 'Evento Test ' . time(),
                ':desc' => 'Descripción del evento de prueba',
                ':fecha_ini' => date('Y-m-d'),
                ':hora_ini' => '10:00:00',
                ':fecha_fin' => date('Y-m-d'),
                ':hora_fin' => '12:00:00',
                ':lugar' => 'Auditorio Principal',
                ':ponente' => 'Dr. Test',
                ':cupo' => 100,
                ':tipo' => 'conferencia'
            ]);
            
            $eventoId = $this->conn->lastInsertId();
            $this->registrarExito("✅ CREATE: Evento creado con ID: $eventoId");
            
            // READ
            $stmt = $this->conn->prepare("SELECT * FROM EVENTOS WHERE ID_EVENTO = :id");
            $stmt->execute([':id' => $eventoId]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($evento) {
                $this->registrarExito("✅ READ: Evento leído - " . $evento['NOMBRE_EVENTO']);
            }
            
            // UPDATE
            $stmt = $this->conn->prepare("UPDATE EVENTOS SET CUPO_MAXIMO = :cupo WHERE ID_EVENTO = :id");
            $stmt->execute([':cupo' => 150, ':id' => $eventoId]);
            $this->registrarExito("✅ UPDATE: Evento actualizado (cupo cambiado a 150)");
            
            // DELETE
            $stmt = $this->conn->prepare("DELETE FROM EVENTOS WHERE ID_EVENTO = :id");
            $stmt->execute([':id' => $eventoId]);
            $this->registrarExito("✅ DELETE: Evento eliminado correctamente");
            
        } catch (Exception $e) {
            $this->registrarError("Error en CRUD de eventos: " . $e->getMessage());
        }
    }
    
    private function testInscripciones() {
        echo "<h2>📝 Fase 5: Sistema de Inscripciones</h2>\n";
        
        try {
            // Crear usuario y evento temporales para la prueba
            $email = 'inscripcion_test_' . time() . '@prueba.com';
            $stmt = $this->conn->prepare("INSERT INTO USUARIOS (NOMBRE_COMPLETO, EMAIL, PASSWORD_HASH, MATRICULA) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Test Inscripcion', $email, password_hash('test', PASSWORD_DEFAULT), 'INS' . time()]);
            $userId = $this->conn->lastInsertId();
            
            $stmt = $this->conn->prepare("INSERT INTO EVENTOS (NOMBRE_EVENTO, FECHA_INICIO, HORA_INICIO, FECHA_FIN, HORA_FIN, CUPO_MAXIMO) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Evento Inscripcion Test', date('Y-m-d'), '10:00:00', date('Y-m-d'), '12:00:00', 50]);
            $eventoId = $this->conn->lastInsertId();
            
            // Crear inscripción
            $stmt = $this->conn->prepare("INSERT INTO INSCRIPCIONES (ID_USUARIO, ID_EVENTO, ESTADO) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $eventoId, 'Inscrito']);
            $inscripcionId = $this->conn->lastInsertId();
            
            $this->registrarExito("✅ Inscripción creada correctamente - ID: $inscripcionId");
            
            // Verificar inscripción
            $stmt = $this->conn->prepare("SELECT * FROM INSCRIPCIONES WHERE ID_INSCRIPCION = ?");
            $stmt->execute([$inscripcionId]);
            $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($inscripcion && $inscripcion['ESTADO'] === 'Inscrito') {
                $this->registrarExito("✅ Inscripción verificada - Estado: " . $inscripcion['ESTADO']);
            }
            
            // Limpiar
            $this->conn->prepare("DELETE FROM INSCRIPCIONES WHERE ID_INSCRIPCION = ?")->execute([$inscripcionId]);
            $this->conn->prepare("DELETE FROM EVENTOS WHERE ID_EVENTO = ?")->execute([$eventoId]);
            $this->conn->prepare("DELETE FROM USUARIOS WHERE ID_USUARIO = ?")->execute([$userId]);
            
        } catch (Exception $e) {
            $this->registrarError("Error en sistema de inscripciones: " . $e->getMessage());
        }
    }
    
    private function testAsistencias() {
        echo "<h2>✅ Fase 6: Sistema de Asistencias</h2>\n";
        
        try {
            // Crear datos de prueba
            $email = 'asistencia_test_' . time() . '@prueba.com';
            $stmt = $this->conn->prepare("INSERT INTO USUARIOS (NOMBRE_COMPLETO, EMAIL, PASSWORD_HASH, MATRICULA) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Test Asistencia', $email, password_hash('test', PASSWORD_DEFAULT), 'ASI' . time()]);
            $userId = $this->conn->lastInsertId();
            
            $stmt = $this->conn->prepare("INSERT INTO EVENTOS (NOMBRE_EVENTO, FECHA_INICIO, HORA_INICIO, FECHA_FIN, HORA_FIN) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Evento Asistencia Test', date('Y-m-d'), '10:00:00', date('Y-m-d'), '12:00:00']);
            $eventoId = $this->conn->lastInsertId();
            
            // Registrar asistencia
            $stmt = $this->conn->prepare("INSERT INTO ASISTENCIA (ID_USUARIO, ID_EVENTO, FECHA, HORA_ENTRADA, ESTADO_ASISTENCIA, METODO_REGISTRO) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $eventoId, date('Y-m-d'), date('H:i:s'), 'Completa', 'QR_SCAN']);
            $asistenciaId = $this->conn->lastInsertId();
            
            $this->registrarExito("✅ Asistencia registrada - ID: $asistenciaId");
            
            // Verificar asistencia
            $stmt = $this->conn->prepare("SELECT * FROM ASISTENCIA WHERE ID_ASISTENCIA = ?");
            $stmt->execute([$asistenciaId]);
            $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($asistencia) {
                $this->registrarExito("✅ Asistencia verificada - Estado: " . $asistencia['ESTADO_ASISTENCIA']);
            }
            
            // Limpiar
            $this->conn->prepare("DELETE FROM ASISTENCIA WHERE ID_ASISTENCIA = ?")->execute([$asistenciaId]);
            $this->conn->prepare("DELETE FROM EVENTOS WHERE ID_EVENTO = ?")->execute([$eventoId]);
            $this->conn->prepare("DELETE FROM USUARIOS WHERE ID_USUARIO = ?")->execute([$userId]);
            
        } catch (Exception $e) {
            $this->registrarError("Error en sistema de asistencias: " . $e->getMessage());
        }
    }
    
    private function testConstancias() {
        echo "<h2>📜 Fase 7: Sistema de Constancias</h2>\n";
        
        try {
            // Crear datos de prueba
            $email = 'constancia_test_' . time() . '@prueba.com';
            $stmt = $this->conn->prepare("INSERT INTO USUARIOS (NOMBRE_COMPLETO, EMAIL, PASSWORD_HASH, MATRICULA) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Test Constancia', $email, password_hash('test', PASSWORD_DEFAULT), 'CON' . time()]);
            $userId = $this->conn->lastInsertId();
            
            $stmt = $this->conn->prepare("INSERT INTO EVENTOS (NOMBRE_EVENTO, FECHA_INICIO, HORA_INICIO, FECHA_FIN, HORA_FIN, GENERA_CONSTANCIA) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Evento Constancia Test', date('Y-m-d'), '10:00:00', date('Y-m-d'), '12:00:00', 1]);
            $eventoId = $this->conn->lastInsertId();
            
            // Generar constancia
            $numeroSerie = 'TEST-' . time() . '-' . $userId;
            $stmt = $this->conn->prepare("INSERT INTO CONSTANCIAS (ID_USUARIO, ID_EVENTO, NUMERO_SERIE) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $eventoId, $numeroSerie]);
            $constanciaId = $this->conn->lastInsertId();
            
            $this->registrarExito("✅ Constancia generada - Número de serie: $numeroSerie");
            
            // Verificar constancia
            $stmt = $this->conn->prepare("SELECT * FROM CONSTANCIAS WHERE ID_CONSTANCIA = ?");
            $stmt->execute([$constanciaId]);
            $constancia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($constancia) {
                $this->registrarExito("✅ Constancia verificada - Serie: " . $constancia['NUMERO_SERIE']);
            }
            
            // Limpiar
            $this->conn->prepare("DELETE FROM CONSTANCIAS WHERE ID_CONSTANCIA = ?")->execute([$constanciaId]);
            $this->conn->prepare("DELETE FROM EVENTOS WHERE ID_EVENTO = ?")->execute([$eventoId]);
            $this->conn->prepare("DELETE FROM USUARIOS WHERE ID_USUARIO = ?")->execute([$userId]);
            
        } catch (Exception $e) {
            $this->registrarError("Error en sistema de constancias: " . $e->getMessage());
        }
    }
    
    private function registrarExito($mensaje) {
        echo "<div class='test success'>✅ $mensaje</div>\n";
        $this->resultados['exitos'][] = $mensaje;
    }
    
    private function registrarError($mensaje) {
        echo "<div class='test error'>❌ $mensaje</div>\n";
        $this->resultados['errores'][] = $mensaje;
    }
    
    private function registrarInfo($mensaje) {
        echo "<div class='test info'>ℹ️ $mensaje</div>\n";
        $this->resultados['info'][] = $mensaje;
    }
    
    private function mostrarResumen() {
        echo "<h2>📊 Resumen de Pruebas</h2>\n";
        
        $totalExitos = count($this->resultados['exitos'] ?? []);
        $totalErrores = count($this->resultados['errores'] ?? []);
        $totalInfo = count($this->resultados['info'] ?? []);
        $total = $totalExitos + $totalErrores;
        
        $porcentajeExito = $total > 0 ? round(($totalExitos / $total) * 100, 2) : 0;
        
        echo "<div class='test info'>";
        echo "<h3>Estadísticas Finales</h3>";
        echo "<ul>";
        echo "<li>✅ Pruebas exitosas: <strong>$totalExitos</strong></li>";
        echo "<li>❌ Pruebas fallidas: <strong>$totalErrores</strong></li>";
        echo "<li>ℹ️ Información: <strong>$totalInfo</strong></li>";
        echo "<li>📈 Porcentaje de éxito: <strong>$porcentajeExito%</strong></li>";
        echo "</ul>";
        echo "</div>\n";
        
        if ($porcentajeExito >= 80) {
            echo "<div class='test success'><h3>🎉 ¡Sistema funcionando correctamente!</h3></div>\n";
        } elseif ($porcentajeExito >= 50) {
            echo "<div class='test info'><h3>⚠️ Sistema parcialmente funcional - Revisar errores</h3></div>\n";
        } else {
            echo "<div class='test error'><h3>🚨 Sistema con problemas críticos - Requiere atención</h3></div>\n";
        }
    }
}

// Ejecutar pruebas
try {
    $tester = new SistemaTester($pdo);
    $tester->ejecutarTodasLasPruebas();
} catch (Exception $e) {
    echo "<div class='test error'>Error fatal: " . $e->getMessage() . "</div>";
}
?>
