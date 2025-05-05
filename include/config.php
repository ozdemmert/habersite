<?php
// Development mode
define('DEVELOPMENT_MODE', true);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habersitesi');
define('CHARSET', 'utf8mb4');

// Establish database connection with error handling
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Set charset
    mysqli_set_charset($conn, CHARSET);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Error creating database: " . mysqli_error($conn));
    }

    // Select database
    if (!mysqli_select_db($conn, DB_NAME)) {
        throw new Exception("Error selecting database: " . mysqli_error($conn));
    }

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        surname VARCHAR(100) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_admin TINYINT(1) NOT NULL DEFAULT 0,
        reset_token VARCHAR(64) DEFAULT NULL,
        reset_token_expiry DATETIME DEFAULT NULL,
        last_login DATETIME DEFAULT NULL,
        remember_token VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY (username),
        UNIQUE KEY (email)
    )";
    mysqli_query($conn, $sql);

    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS news (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        views INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        description TEXT,
        PRIMARY KEY (id),
        UNIQUE KEY (slug)
    )";
    mysqli_query($conn, $sql);

    // Create post_category table (for many-to-many relationship)
    $sql = "CREATE TABLE IF NOT EXISTS news_category (
        news_id INT(11) NOT NULL,
        category_id INT(11) NOT NULL,
        PRIMARY KEY (news_id, category_id),
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);

    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS dosya (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        views INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS dosya_category (
        dosya_id INT(11) NOT NULL,
        category_id INT(11) NOT NULL,
        PRIMARY KEY (dosya_id, category_id),
        FOREIGN KEY (dosya_id) REFERENCES dosya(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS storymaps (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        views INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS storymaps_category (
        storymaps_id INT(11) NOT NULL,
        category_id INT(11) NOT NULL,
        PRIMARY KEY (storymaps_id, category_id),
        FOREIGN KEY (storymaps_id) REFERENCES storymaps(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS timeline (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        new_date DATE NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        views INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS timeline_category (
        timeline_id INT(11) NOT NULL,
        category_id INT(11) NOT NULL,
        PRIMARY KEY (timeline_id, category_id),
        FOREIGN KEY (timeline_id) REFERENCES timeline(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS 4nokta1 (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        explanation VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        authors VARCHAR(255) NOT NULL,
        authors_info VARCHAR(255) NOT NULL,
        authors_image VARCHAR(755) NOT NULL,
        authors_comments TEXT NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        views INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS 4nokta1_category (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        description TEXT,
        PRIMARY KEY (id),
        UNIQUE KEY (slug)
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS portre (
        id INT(11) NOT NULL AUTO_INCREMENT,
        first_name VARCHAR(255) NOT NULL,
        lastname VARCHAR(255) NOT NULL,
        biography TEXT NOT NULL,
        portre_image VARCHAR(255) NOT NULL,
        degree VARCHAR(255) NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        quote TEXT DEFAULT NULL,
        user_id INT(11) NOT NULL,
        username VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS social (
        id INT(11) NOT NULL AUTO_INCREMENT,
        platform VARCHAR(100) NOT NULL,
        social_url VARCHAR(255) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY (platform)
    )";
    mysqli_query($conn, $sql);

    // Sections table
    $sql = "CREATE TABLE IF NOT EXISTS sections (
        section_name VARCHAR(50) PRIMARY KEY,
        news_id INT(11),
        youtube_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $sql);

    // Hafıza tablosu
    $sql = "CREATE TABLE IF NOT EXISTS hafiza (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        featured_image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published TINYINT(1) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL,
        meta_description VARCHAR(160) DEFAULT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        views INT(11) DEFAULT 0,
        new_date DATE DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY (slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS hafiza_category (
        hafiza_id INT(11) NOT NULL,
        category_id INT(11) NOT NULL,
        PRIMARY KEY (hafiza_id, category_id),
        FOREIGN KEY (hafiza_id) REFERENCES hafiza(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $sql);

    // Insert default admin user if not exists
    $check_admin = "SELECT * FROM users WHERE username = 'admin'";
    $result = mysqli_query($conn, $check_admin);

    if (mysqli_num_rows($result) == 0) {
        // Hash the password properly
        $password = '123456789';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, is_admin) 
                VALUES ('admin', 'admin@example.com', '$hashed_password', 1)";
        mysqli_query($conn, $sql);
    }
    
} catch (Exception $e) {
    // Log the error and display a user-friendly message
    error_log("Database Error: " . $e->getMessage());

    // Show detailed error in development mode
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo "<div style='color:red;'>Database Error: " . $e->getMessage() . "</div>";
    } else {
        echo "<div style='color:red;'>A database error occurred. Please try again later or contact the administrator.</div>";
    }
    // You can redirect to an error page if needed
    // header("Location: error.php");
    // exit;
}

// Set sensible PHP security settings
ini_set('display_errors', 1); // Show errors in development
?>