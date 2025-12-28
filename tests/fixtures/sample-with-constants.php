<?php

declare(strict_types=1);

// File that uses extension constants

// PDO constants
$attr = PDO::ATTR_ERRMODE;
$mode = PDO::ERRMODE_EXCEPTION;

// cURL constants
$opt = CURLOPT_RETURNTRANSFER;
$info = CURLINFO_HTTP_CODE;
