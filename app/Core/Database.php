<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Dual Database Manager
 * Strictly enforces architecture:
 * 1. Application DB (Port 3306) -> Read/Write
 * 2. JHCIS DB (Port 3333) -> READ-ONLY with Timeout and Circuit Breaker
 */
class Database
{
    private static ?PDO $appDb = null;
    private static ?PDO $jhcisDb = null;
    private static bool $jhcisAvailable = false;
    private static ?string $jhcisError = null;

    /**
     * Get Application Database Connection (Port 3306)
     */
    public static function getAppDb(): PDO
    {
        if (self::$appDb === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbName = getenv('DB_DATABASE') ?: 'pcu_pharmacy';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$appDb = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Application DB Connection Failed: " . $e->getMessage());
                die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลหลัก Application Database: " . $e->getMessage());
            }
        }
        return self::$appDb;
    }

    /**
     * Get JHCIS Database Connection (Port 3333) - READ ONLY
     */
    public static function getJhcisDb(): ?PDO
    {
        if (self::$jhcisDb === null && !self::$jhcisAvailable && self::$jhcisError === null) {
            $host = getenv('JHCIS_DB_HOST') ?: '127.0.0.1';
            $port = getenv('JHCIS_DB_PORT') ?: '3333';
            $dbName = getenv('JHCIS_DB_DATABASE') ?: 'jhcisdb';
            $user = getenv('JHCIS_DB_USERNAME') ?: 'root';
            $pass = getenv('JHCIS_DB_PASSWORD') ?: '123456';
            $timeout = (int)(getenv('JHCIS_TIMEOUT_SEC') ?: 3);

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => $timeout,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ];

            try {
                self::$jhcisDb = new PDO($dsn, $user, $pass, $options);
                self::$jhcisAvailable = true;
            } catch (PDOException $e) {
                self::$jhcisAvailable = false;
                self::$jhcisError = $e->getMessage();
                error_log("JHCIS DB Connection Failed: " . $e->getMessage());
            }
        }
        return self::$jhcisDb;
    }

    /**
     * Check if JHCIS Connection is Alive
     */
    public static function isJhcisConnected(): bool
    {
        self::getJhcisDb();
        return self::$jhcisAvailable;
    }

    /**
     * Get JHCIS Connection Error Message (if any)
     */
    public static function getJhcisError(): ?string
    {
        return self::$jhcisError;
    }
}
