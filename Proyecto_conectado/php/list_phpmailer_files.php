<?php
// php/list_phpmailer_files.php
// Devuelve JSON con información útil para depurar la carga de PHPMailer y los logs SMTP/sendmail.

header('Content-Type: application/json; charset=utf-8');

$base = __DIR__;
$result = [];

// Files to check
$files = [
    'composer_autoload' => $base . '/vendor/autoload.php',
    'phpmailer_src_dir' => $base . '/PHPMailer/src',
    'phpmailer_src_php' => $base . '/PHPMailer/src/PHPMailer.php',
    'phpmailer_root_php' => $base . '/PHPMailer.php',
    'phpmailer_exception' => $base . '/Exception.php',
    'phpmailer_smtp' => $base . '/SMTP.php',
    'smtp_config' => $base . '/smtp_config.php',
    'smtp_debug_log' => $base . '/smtp_debug.log',
];

foreach ($files as $k => $p) {
    $result['files'][$k] = ['path' => $p, 'exists' => file_exists($p)];
}

// list php files in folder
$phpFiles = glob($base . DIRECTORY_SEPARATOR . '*.php');
$result['php_files_in_dir'] = array_map('basename', $phpFiles ?: []);

// list files in PHPMailer/src if exists
if (is_dir($base . '/PHPMailer/src')) {
    $srcFiles = glob($base . '/PHPMailer/src/*.php');
    $result['phpmailer_src_files'] = array_map('basename', $srcFiles ?: []);
} else {
    $result['phpmailer_src_files'] = [];
}

// Whether the namespaced class is available
$result['class_exists_namespaced'] = class_exists('PHPMailer\\PHPMailer\\PHPMailer');

// Attempt to include PHPMailer files lightly to see if they define the class (no fatal errors expected)
ob_start();
try {
    if (file_exists($base . '/PHPMailer.php')) {
        @include_once $base . '/PHPMailer.php';
    }
    if (file_exists($base . '/PHPMailer/src/PHPMailer.php')) {
        @include_once $base . '/PHPMailer/src/PHPMailer.php';
    }
    if (file_exists($base . '/vendor/autoload.php')) {
        @include_once $base . '/vendor/autoload.php';
    }
} catch (Throwable $t) {
    $result['include_exception'] = $t->getMessage();
}
$out = ob_get_clean();
if ($out) $result['include_output'] = substr($out, 0, 1000);

$result['class_exists_after_include'] = class_exists('PHPMailer\\PHPMailer\\PHPMailer');

// Tail of smtp_debug.log
if (file_exists($base . '/smtp_debug.log')) {
    $lines = file($base . '/smtp_debug.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tail = array_slice($lines, -80);
    $result['smtp_debug_tail'] = $tail;
} else {
    $result['smtp_debug_tail'] = [];
}

// Tail of C:\\xampp\\sendmail\\error.log if accessible
$sendmailLog = 'C:\\xampp\\sendmail\\error.log';
if (file_exists($sendmailLog)) {
    $lines = @file($sendmailLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        $result['sendmail_error_tail'] = array_slice($lines, -80);
    } else {
        $result['sendmail_error_tail'] = ['unable_to_read_sendmail_log'];
    }
} else {
    $result['sendmail_error_tail'] = [];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>
