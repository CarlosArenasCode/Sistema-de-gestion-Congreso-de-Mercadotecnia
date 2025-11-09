<?php
/**
 * Utilidades para Oracle Database
 * 
 * Funciones helper para facilitar la migración de MySQL a Oracle
 * y trabajar con características específicas de Oracle
 */

/**
 * Clase OracleHelper
 * Contiene métodos estáticos útiles para trabajar con Oracle
 */
class OracleHelper {
    
    /**
     * Convierte una consulta MySQL LIMIT a Oracle FETCH FIRST
     * 
     * @param string $sql Consulta SQL
     * @param int $limit Número de registros
     * @param int $offset Offset (opcional)
     * @return string SQL adaptado para Oracle
     */
    public static function convertLimit($sql, $limit, $offset = 0) {
        if ($offset > 0) {
            return $sql . " OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY";
        } else {
            return $sql . " FETCH FIRST {$limit} ROWS ONLY";
        }
    }
    
    /**
     * Obtiene el ID del último registro insertado para una tabla
     * 
     * @param PDO $pdo Conexión PDO
     * @param string $table Nombre de la tabla
     * @param string $idColumn Nombre de la columna ID (default: id_{tabla})
     * @return int|null ID del último registro
     */
    public static function getLastInsertId($pdo, $table, $idColumn = null) {
        if ($idColumn === null) {
            $idColumn = "id_" . rtrim($table, 's'); // Remueve 's' del plural
        }
        
        try {
            $sql = "SELECT MAX({$idColumn}) as last_id FROM {$table}";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['last_id'];
        } catch (PDOException $e) {
            error_log("Error obteniendo último ID de {$table}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Convierte booleano de Oracle (0/1) a PHP (true/false)
     * 
     * @param int|string $value Valor de Oracle
     * @return bool Valor booleano PHP
     */
    public static function toBoolean($value) {
        return (bool)$value;
    }
    
    /**
     * Convierte booleano de PHP a Oracle (0/1)
     * 
     * @param bool $value Valor booleano PHP
     * @return int Valor para Oracle (0 o 1)
     */
    public static function fromBoolean($value) {
        return $value ? 1 : 0;
    }
    
    /**
     * Formatea una fecha de Oracle al formato deseado
     * 
     * @param string $oracleDate Fecha de Oracle
     * @param string $format Formato de salida (default: d/m/Y H:i)
     * @return string Fecha formateada
     */
    public static function formatDate($oracleDate, $format = 'd/m/Y H:i') {
        if (empty($oracleDate)) return '';
        
        try {
            $date = new DateTime($oracleDate);
            return $date->format($format);
        } catch (Exception $e) {
            error_log("Error formateando fecha: " . $e->getMessage());
            return $oracleDate;
        }
    }
    
    /**
     * Escapa caracteres especiales para LIKE en Oracle
     * 
     * @param string $string Cadena a escapar
     * @param string $escapeChar Caracter de escape (default: \)
     * @return string Cadena escapada
     */
    public static function escapeLike($string, $escapeChar = '\\') {
        $string = str_replace($escapeChar, $escapeChar.$escapeChar, $string);
        $string = str_replace('%', $escapeChar.'%', $string);
        $string = str_replace('_', $escapeChar.'_', $string);
        return $string;
    }
    
    /**
     * Construye una cláusula WHERE con LIKE case-insensitive
     * 
     * @param string $column Nombre de la columna
     * @param string $value Valor a buscar
     * @param string $placeholder Placeholder para prepared statement
     * @return array [sql, param] SQL y valor del parámetro
     */
    public static function buildLikeClause($column, $value, $placeholder = ':search') {
        $escapedValue = self::escapeLike($value);
        $sql = "UPPER({$column}) LIKE UPPER({$placeholder})";
        $param = "%{$escapedValue}%";
        return [$sql, $param];
    }
    
    /**
     * Convierte TIME de MySQL a INTERVAL de Oracle
     * Formato MySQL: HH:MM:SS
     * Formato Oracle: INTERVAL 'HH:MI:SS' HOUR TO SECOND
     * 
     * @param string $time Tiempo en formato MySQL (HH:MM:SS)
     * @return string Expresión INTERVAL para Oracle
     */
    public static function timeToInterval($time) {
        if (empty($time)) return "INTERVAL '00:00:00' HOUR TO SECOND";
        return "INTERVAL '{$time}' HOUR TO SECOND";
    }
    
    /**
     * Convierte INTERVAL de Oracle a formato de tiempo legible
     * 
     * @param string $interval Intervalo de Oracle
     * @return string Tiempo en formato HH:MM:SS
     */
    public static function intervalToTime($interval) {
        // Parsear el intervalo (formato: +00 00:00:00.000000)
        if (preg_match('/\+(\d+)\s(\d+):(\d+):(\d+)/', $interval, $matches)) {
            $days = intval($matches[1]);
            $hours = intval($matches[2]) + ($days * 24);
            $minutes = $matches[3];
            $seconds = $matches[4];
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return '00:00:00';
    }
    
    /**
     * Ejecuta múltiples consultas en una transacción
     * 
     * @param PDO $pdo Conexión PDO
     * @param array $queries Array de queries a ejecutar
     * @return bool True si todo se ejecutó correctamente
     * @throws Exception Si hay un error
     */
    public static function executeTransaction($pdo, array $queries) {
        try {
            $pdo->beginTransaction();
            
            foreach ($queries as $query) {
                if (is_array($query)) {
                    // Si es array, el primer elemento es el SQL y el segundo los parámetros
                    $stmt = $pdo->prepare($query[0]);
                    $stmt->execute($query[1]);
                } else {
                    // Si es string, ejecutar directamente
                    $pdo->exec($query);
                }
            }
            
            $pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en transacción: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene información de la versión de Oracle
     * 
     * @param PDO $pdo Conexión PDO
     * @return array Información de la base de datos
     */
    public static function getDatabaseInfo($pdo) {
        try {
            $stmt = $pdo->query("
                SELECT 
                    BANNER as version,
                    SYS_CONTEXT('USERENV', 'DB_NAME') as nombre_bd,
                    SYS_CONTEXT('USERENV', 'CON_NAME') as contenedor,
                    SYS_CONTEXT('USERENV', 'SESSION_USER') as usuario
                FROM V\$VERSION 
                WHERE BANNER LIKE 'Oracle%'
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error obteniendo info de BD: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica si una tabla existe en el esquema actual
     * 
     * @param PDO $pdo Conexión PDO
     * @param string $tableName Nombre de la tabla
     * @return bool True si existe
     */
    public static function tableExists($pdo, $tableName) {
        try {
            $tableUpper = strtoupper($tableName);
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM user_tables 
                WHERE table_name = :table
            ");
            $stmt->execute([':table' => $tableUpper]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error verificando tabla: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el conteo de registros de una tabla
     * 
     * @param PDO $pdo Conexión PDO
     * @param string $tableName Nombre de la tabla
     * @param string $whereClause Cláusula WHERE opcional
     * @return int Número de registros
     */
    public static function getRecordCount($pdo, $tableName, $whereClause = '') {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$tableName}";
            if (!empty($whereClause)) {
                $sql .= " WHERE {$whereClause}";
            }
            
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count']);
            
        } catch (PDOException $e) {
            error_log("Error contando registros: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Construye una consulta SELECT con paginación para Oracle
     * 
     * @param string $baseQuery Consulta base sin LIMIT
     * @param int $page Número de página (1-based)
     * @param int $perPage Registros por página
     * @return string Consulta con paginación
     */
    public static function paginate($baseQuery, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        return self::convertLimit($baseQuery, $perPage, $offset);
    }
    
    /**
     * Sanitiza nombre de tabla/columna para prevenir SQL injection
     * 
     * @param string $identifier Nombre de tabla o columna
     * @return string Identificador sanitizado
     */
    public static function sanitizeIdentifier($identifier) {
        // Remover cualquier caracter que no sea alfanumérico o underscore
        return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
    }
}

/**
 * Funciones globales para compatibilidad rápida
 * (wrappers de la clase OracleHelper)
 */

function oracle_last_insert_id($pdo, $table, $idColumn = null) {
    return OracleHelper::getLastInsertId($pdo, $table, $idColumn);
}

function oracle_bool($value) {
    return OracleHelper::toBoolean($value);
}

function oracle_format_date($date, $format = 'd/m/Y H:i') {
    return OracleHelper::formatDate($date, $format);
}

function oracle_escape_like($string) {
    return OracleHelper::escapeLike($string);
}

?>
