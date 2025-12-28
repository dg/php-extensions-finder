<?php

declare(strict_types=1);

// This file contains code that uses various PHP extensions

// PDO extension
$pdo = new PDO('mysql:host=localhost', 'user', 'pass');
$stmt = $pdo->prepare('SELECT * FROM users');

// cURL extension
$ch = curl_init('https://example.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// mbstring extension
$length = mb_strlen('čeština', 'UTF-8');
$upper = mb_strtoupper('hello', 'UTF-8');

// Sodium extension (if available)
if (function_exists('sodium_crypto_generichash')) {
	$hash = sodium_crypto_generichash('message');
}
