<?php
/**
 * Database Configuration
 * 
 * This module handles database connections using SQLite (for portability)
 * with PDO for secure parameterized queries.
 * 
 * Security features:
 * - PDO prepared statements prevent SQL injection
 * - Database file stored outside web root
 * - Connection uses persistent error mode
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

class Database {
    private static ?PDO $instance = null;
    private static string $dbPath;

    /**
     * Get database connection (singleton pattern)
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$dbPath = __DIR__ . '/../data/tlevel_hub.sqlite';
            $dbDir = dirname(self::$dbPath);
            
            // Create data directory if it doesn't exist
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0750, true);
            }

            self::$instance = new PDO(
                'sqlite:' . self::$dbPath,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            // Enable WAL mode for better concurrent access
            self::$instance->exec('PRAGMA journal_mode=WAL');
            // Enable foreign keys
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }
        return self::$instance;
    }

    /**
     * Initialize database tables
     * Creates all required tables if they don't exist
     * 
     * @return void
     */
    public static function initializeTables(): void {
        $db = self::getConnection();
        
        // Applications table
        $db->exec("
            CREATE TABLE IF NOT EXISTS applications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ref_number TEXT UNIQUE NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                date_of_birth TEXT NOT NULL,
                nationality TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT DEFAULT '',
                country TEXT NOT NULL,
                school TEXT NOT NULL,
                year_of_study TEXT NOT NULL,
                tlevel_course TEXT NOT NULL,
                preferred_location TEXT NOT NULL,
                preferred_start TEXT NOT NULL,
                preferred_duration TEXT DEFAULT '',
                req_accommodation INTEGER DEFAULT 0,
                req_transport INTEGER DEFAULT 0,
                req_visa INTEGER DEFAULT 0,
                req_language INTEGER DEFAULT 0,
                heard_from TEXT DEFAULT '',
                motivation TEXT NOT NULL,
                skills TEXT DEFAULT '',
                additional_needs TEXT DEFAULT '',
                status TEXT DEFAULT 'received',
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                reviewed_at DATETIME,
                admin_notes TEXT DEFAULT ''
            )
        ");

        // Contact enquiries table
        $db->exec("
            CREATE TABLE IF NOT EXISTS contact_enquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                subject TEXT NOT NULL,
                message TEXT NOT NULL,
                status TEXT DEFAULT 'unread',
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                replied_at DATETIME
            )
        ");

        // Admin users table
        $db->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                display_name TEXT NOT NULL,
                role TEXT DEFAULT 'admin',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME
            )
        ");

        // Session tokens table for admin auth
        $db->exec("
            CREATE TABLE IF NOT EXISTS auth_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT UNIQUE NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
            )
        ");

        // Audit log for tracking admin actions
        $db->exec("
            CREATE TABLE IF NOT EXISTS audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action TEXT NOT NULL,
                details TEXT DEFAULT '',
                ip_address TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
            )
        ");

        // Newsletter / mailing list
        $db->exec("
            CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'active'
            )
        ");

        // Course feedback
        $db->exec("
            CREATE TABLE IF NOT EXISTS course_feedback (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_name TEXT NOT NULL,
                email TEXT DEFAULT '',
                rating INTEGER CHECK(rating >= 1 AND rating <= 5),
                feedback_text TEXT DEFAULT '',
                status TEXT DEFAULT 'new',
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Migrate older installs that created course_feedback before the
        // email/status columns existed. SQLite has no "ADD COLUMN IF NOT
        // EXISTS", so we probe the schema and add columns only if missing.
        $existingCols = array_column($db->query("PRAGMA table_info(course_feedback)")->fetchAll(), 'name');
        if (!in_array('email', $existingCols, true)) {
            $db->exec("ALTER TABLE course_feedback ADD COLUMN email TEXT DEFAULT ''");
        }
        if (!in_array('status', $existingCols, true)) {
            $db->exec("ALTER TABLE course_feedback ADD COLUMN status TEXT DEFAULT 'new'");
        }
    }

    /**
     * Close the database connection
     * 
     * @return void
     */
    public static function close(): void {
        self::$instance = null;
    }
}