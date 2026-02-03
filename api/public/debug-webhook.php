<?php
/**
 * Diagnóstico del webhook - simula la petición de Mercado Pago.
 * Subir a public_html/api/public/debug-webhook.php
 * Acceder: https://cesped365.com/api/debug-webhook.php
 *
 * BORRAR después de usar.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG WEBHOOK ===\n\n";

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "\n!!! ERROR FATAL !!!\n";
        echo $err['message'] . "\n";
        echo $err['file'] . ":" . $err['line'] . "\n";
    }
});

// Simular exactamente lo que envía Mercado Pago
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/payment/webhook';
$_SERVER['PATH_INFO'] = '/payment/webhook';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$body = '{"type":"subscription_preapproval","entity":"preapproval","id":"123456","data":{"id":"123456"}}';

// php://input se lee una vez; preparar stream
file_put_contents('php://stdin', $body); // No funciona
// En su lugar, crear archivo temporal y wrappear
$tmp = tempnam(sys_get_temp_dir(), 'mp');
file_put_contents($tmp, $body);

// Usar stream wrapper para que file_get_contents('php://input') devuelva nuestro body
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'MockPhpStream');

class MockPhpStream {
    private $tmp;
    private $pos = 0;
    public function stream_open($path, $mode, $options, &$opened_path) {
        if (strpos($path, 'php://input') !== false) {
            $this->tmp = $GLOBALS['_DEBUG_WEBHOOK_BODY'] ?? '';
            return true;
        }
        return false;
    }
    public function stream_read($count) {
        $ret = substr($this->tmp, $this->pos, $count);
        $this->pos += strlen($ret);
        return $ret;
    }
    public function stream_eof() { return $this->pos >= strlen($this->tmp); }
    public function stream_stat() { return []; }
    public function stream_seek($offset, $whence) { return false; }
}

// Eso es muy frágil. Mejor: script simple paso a paso sin ejecutar el webhook real.
stream_wrapper_restore('php');

// Enfoque simple: chequeos paso a paso
echo "1. PHP " . PHP_VERSION . " OK\n";

$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($vendor)) {
    die("2. ERROR: vendor/autoload.php no existe. Ejecutar composer install.\n");
}
require $vendor;
echo "2. vendor/autoload OK\n";

$mpPath = dirname(__DIR__) . '/vendor/mercadopago/dx-php/src/MercadoPago/MercadoPagoConfig.php';
echo "3. MercadoPago SDK:\n";
echo "   Ruta esperada: $mpPath\n";
echo "   Archivo existe: " . (file_exists($mpPath) ? 'SÍ' : 'NO') . "\n";
$mpDir = dirname(__DIR__) . '/vendor/mercadopago';
if (is_dir($mpDir)) {
    $list = @scandir($mpDir) ?: [];
    $list = array_diff($list, ['.', '..']);
    echo "   Contenido vendor/mercadopago/: " . (empty($list) ? '(vacío)' : implode(', ', $list)) . "\n";
}
if (!class_exists('MercadoPago\MercadoPagoConfig')) {
    echo "   class_exists=FALLO - Intentando require directo...\n";
    if (file_exists($mpPath)) {
        require_once $mpPath;
        echo "   require OK. class_exists ahora: " . (class_exists('MercadoPago\MercadoPagoConfig') ? 'SÍ' : 'NO') . "\n";
    }
    if (!class_exists('MercadoPago\MercadoPagoConfig')) {
        die("\n   ERROR: MercadoPago SDK no cargado. Verificar que exista:\n   vendor/mercadopago/dx-php/src/MercadoPago/MercadoPagoConfig.php\n");
    }
}
echo "   MercadoPago SDK OK\n";

$env = dirname(__DIR__) . '/.env';
if (!file_exists($env)) {
    die("4. ERROR: .env no existe en api/\n");
}
echo "4. .env existe OK\n";

// Cargar .env manualmente
$envContent = file_get_contents($env);
foreach (explode("\n", $envContent) as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') continue;
    if (preg_match('/^([A-Za-z0-9_.]+)=(.*)$/', $line, $m)) {
        $v = trim($m[2], " \t\"'");
        putenv($m[1] . '=' . $v);
        $_ENV[$m[1]] = $v;
    }
}
$token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? getenv('MERCADOPAGO_ACCESS_TOKEN') ?? '';
echo "5. MERCADOPAGO_ACCESS_TOKEN: " . (strlen($token) > 10 ? substr($token, 0, 15) . '...' : 'VACÍO') . "\n";

echo "\n6. Boot CodeIgniter + PaymentController...\n";
try {
    if (!defined('FCPATH')) {
        define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    }
    chdir(FCPATH);
    require FCPATH . '../app/Config/Paths.php';
    $paths = new \Config\Paths();
    require $paths->systemDirectory . '/Boot.php';
    \CodeIgniter\Boot::bootConsole($paths);
    
    $ctrl = new \App\Controllers\Api\PaymentController();
    echo "   PaymentController OK\n";
    
    echo "\n7. Simulando webhook()...\n";
    $resp = $ctrl->webhook();
    echo "   Status: " . $resp->getStatusCode() . " OK\n";
} catch (\Throwable $e) {
    echo "\n!!! ERROR !!!\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack:\n" . $e->getTraceAsString() . "\n";
}

echo "\n--- BORRAR debug-webhook.php después de usar ---\n";
