<?php
/**
 * Cliente PHP para el servicio de WhatsApp
 * Se comunica con el servicio Node.js en Docker
 */

class WhatsAppClient {
    private $serviceUrl;
    
    public function __construct($serviceUrl = 'http://whatsapp:3001') {
        $this->serviceUrl = $serviceUrl;
    }
    
    /**
     * Enviar código de verificación por WhatsApp
     * 
     * @param string $phone Número de teléfono (formato: +524491234567 o 4491234567)
     * @param string $code Código de verificación de 6 dígitos
     * @param string $name Nombre del usuario (opcional)
     * @return array Resultado del envío
     */
    public function sendVerificationCode($phone, $code, $name = '') {
        try {
            // Preparar datos
            $data = [
                'phone' => $phone,
                'code' => $code,
                'name' => $name
            ];
            
            // Realizar petición POST al servicio
            $response = $this->makeRequest('/send-verification-code', 'POST', $data);
            
            // Registrar en log
            $this->logSend($phone, $code, $response);
            
            return $response;
            
        } catch (Exception $e) {
            error_log("Error al enviar código WhatsApp: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar estado del servicio de WhatsApp
     * 
     * @return array Estado del servicio
     */
    public function checkHealth() {
        try {
            return $this->makeRequest('/health', 'GET');
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar mensaje de prueba
     * 
     * @param string $phone Número de teléfono
     * @return array Resultado del envío
     */
    public function sendTest($phone) {
        try {
            $data = ['phone' => $phone];
            return $this->makeRequest('/test-send', 'POST', $data);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Realizar petición HTTP al servicio
     * 
     * @param string $endpoint Endpoint del servicio
     * @param string $method Método HTTP (GET, POST)
     * @param array $data Datos a enviar (para POST)
     * @return array Respuesta decodificada
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->serviceUrl . $endpoint;
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Error de conexión: " . $error);
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("Error HTTP $httpCode: " . $response);
        }
        
        $decoded = json_decode($response, true);
        
        if ($decoded === null) {
            throw new Exception("Respuesta inválida del servicio");
        }
        
        return $decoded;
    }
    
    /**
     * Registrar envío en log
     */
    private function logSend($phone, $code, $response) {
        $logDir = __DIR__ . '/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/whatsapp_client.log';
        $timestamp = date('Y-m-d H:i:s');
        $success = isset($response['success']) && $response['success'] ? 'OK' : 'FAIL';
        $message = isset($response['error']) ? $response['error'] : 'Enviado correctamente';
        
        $logEntry = sprintf(
            "[%s] %s | Teléfono: %s | Código: %s | %s\n",
            $timestamp,
            $success,
            $phone,
            $code,
            $message
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// Ejemplo de uso:
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    echo "=== Cliente WhatsApp - Ejemplos de uso ===\n\n";
    
    $client = new WhatsAppClient();
    
    // Verificar estado
    echo "1. Verificando estado del servicio...\n";
    $health = $client->checkHealth();
    echo json_encode($health, JSON_PRETTY_PRINT) . "\n\n";
    
    // Ejemplo de envío (comentado, descomentar para probar)
    /*
    echo "2. Enviando código de prueba...\n";
    $result = $client->sendVerificationCode(
        '+524491234567',  // Número del usuario
        '123456',         // Código de verificación
        'Juan Pérez'      // Nombre del usuario
    );
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    */
}
?>
