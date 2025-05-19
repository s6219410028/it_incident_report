<?php
// db_config.php

// common settings
$host    = 'localhost';
$charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ── HRM database ───────────────────────────────────────────────────────────────
$hrmDb   = 'admin_hrm';
$hrmUser = 'antonio';
$hrmPass = 'Vw23*m43s';
$hrmDsn  = "mysql:host={$host};dbname={$hrmDb};charset={$charset}";

try {
    $pdoHrm = new PDO($hrmDsn, $hrmUser, $hrmPass, $options);
} catch (PDOException $e) {
    throw new PDOException("HRM DB connection failed: " . $e->getMessage(), (int)$e->getCode());
}

// ── ITD database ───────────────────────────────────────────────────────────────
$itdDb   = 'admin_itd';           // ← change this to your ITD database name
$itdUser = 'admin_itd';     // ← your ITD DB user
$itdPass = 'Pf44~k67g';     // ← your ITD DB password
$itdDsn  = "mysql:host={$host};dbname={$itdDb};charset={$charset}";

try {
    $pdoItd = new PDO($itdDsn, $itdUser, $itdPass, $options);
} catch (PDOException $e) {
    throw new PDOException("ITD DB connection failed: " . $e->getMessage(), (int)$e->getCode());
}


/**
 * @param string $which  Either 'hrm' or 'itd'
 * @return PDO
 */
function getDb(string $which): PDO
{
    global $pdoHrm, $pdoItd;
    if ($which === 'hrm') {
        return $pdoHrm;
    } elseif ($which === 'itd') {
        return $pdoItd;
    }
    throw new InvalidArgumentException("Unknown database “{$which}”");
}

// DEBUG: confirm getDb exists
if (!function_exists('getDb')) {
    die('getDb() is not defined!');
}

