<?php
declare(strict_types=1);
$DB_HOST = $_ENV['DB_HOST'] ?? '127.0.0.1';
$DB_NAME = $_ENV['DB_NAME'] ?? 'l9_gym';
$DB_USER = $_ENV['DB_USER'] ?? 'root';
$DB_PASS = $_ENV['DB_PASS'] ?? '';
$DB_CHARSET = 'utf8mb4';
$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];
try { $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options); }
catch (PDOException $e) { http_response_code(500); exit('Database connection failed.'); }
