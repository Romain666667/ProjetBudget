<?php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $host = 'localhost';
  $db   = 'budgett';
  $user = 'root';
  $pass = 'NouveauMotDePasse';
  $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  return $pdo;
}
