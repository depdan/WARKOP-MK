<?php
declare(strict_types=1);

define('APP_NAME', 'WARKOP MK — Kasir & Pemesanan');
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim(str_replace(' ', '%20', $scriptDir), '/');
define('BASE_URL', $baseUrl);

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
