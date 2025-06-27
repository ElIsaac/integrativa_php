<?php
namespace HttpClient;

class HttpClient {
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $baseUrl, int $timeout = 30) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Realiza una petición HTTP limitada a GET, POST y PUT con Content-Type application/json
     * 
     * @param string $method Método HTTP (solo 'GET', 'POST', 'PUT')
     * @param string $endpoint Ruta o endpoint (sin base URL)
     * @param array|null $data Datos a enviar (opcional)
     * @return string Respuesta en formato string
     * @throws \Exception Si método no permitido o error en la petición
     */
    public function request(string $method, string $endpoint, ?array $data = null): string {
        $method = strtoupper($method);

        if (!in_array($method, ['GET', 'POST', 'PUT'])) {
            throw new \Exception("Método HTTP no permitido: {$method}");
        }

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $headers = ['Content-Type: application/json'];

        if ($method === 'GET') {
            if ($data !== null) {
                $query = http_build_query($data);
                $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
            }
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else { // POST o PUT
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            $payload = $data !== null ? json_encode($data) : '{}';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Length: ' . strlen($payload);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Error en cURL: {$errorMsg}");
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw new \Exception("Error HTTP {$status}: {$response}");
        }

        return $response;
    }
}
