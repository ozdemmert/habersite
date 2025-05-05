<?php
require_once 'config.php';

class Database
{
    protected $conn;

    public function __construct()
    {
        $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$this->conn) {
            die("Veritabanı bağlantı hatası: " . mysqli_connect_error());
        }
        mysqli_set_charset($this->conn, "utf8mb4");
    }

    public function checkConnection()
    {
        return mysqli_ping($this->conn);
    }

    protected function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return mysqli_real_escape_string($this->conn, trim($input));
    }
}

class User extends Database
{
    public function create($username, $email, $password, $is_admin = 0, $name = '', $surname = '', $image = null)
    {
        $username = $this->sanitize($username);
        $email = $this->sanitize($email);
        $name = $this->sanitize($name);
        $surname = $this->sanitize($surname);
        $image = $this->sanitize($image);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, name, surname, image, is_admin) 
                VALUES ('$username', '$email', '$hashed_password', '$name', '$surname', '$image', $is_admin)";
        return mysqli_query($this->conn, $sql);
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $updates = [];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            $value = $this->sanitize($value);
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM users WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM users WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM users";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getByUsername($username)
    {
        $username = $this->sanitize($username);
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    public function updateRememberToken($id, $token) {
        $id = (int)$id;
        $token = $this->sanitize($token);
        $sql = "UPDATE users SET remember_token = '$token' WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }
    
    public function updateLastLogin($id) {
        $id = (int)$id;
        $sql = "UPDATE users SET last_login = NOW() WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }
    
    public function login($username, $password) {
        $username = $this->sanitize($username);
        $user = $this->getByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
}

class News extends Database
{
    protected function countFeatured($excludeId = null)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM news WHERE is_featured = 1";
        if ($excludeId !== null) {
            $excludeId = (int) $excludeId;
            $sql .= " AND id != {$excludeId}";
        }
        $row = $this->conn->query($sql)->fetch_assoc();
        return (int) $row['cnt'];
    }

    /**
     * Generate a URL-friendly, unique slug based on title.
     */
    protected function makeUniqueSlug(string $title): string
    {
        // 1) basic slugify
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' ', '', '–', '—'],
            ['i', 'g', 'u', 's', 'o', 'c', '-', '-', '-', '-'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // 2) ensure uniqueness by appending -2, -3, etc.
        $base = $slug;
        $i = 1;
        while (true) {
            $escaped = mysqli_real_escape_string($this->conn, $slug);
            $check = mysqli_query(
                $this->conn,
                "SELECT 1 FROM news WHERE slug = '$escaped' LIMIT 1"
            );
            if (mysqli_num_rows($check) === 0) {
                break;
            }
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function create($data)
    {
        $data = $this->sanitize($data);

        // If they asked to feature it, but we're already at 3, silently demote:
        if (!empty($data['is_featured']) && $this->countFeatured() >= 3) {
            $data['is_featured'] = 0;
            $this->warningMessage = "Maksimum 3 öne çıkan haberiniz var; bu haber öne çıkan olarak kaydedilmedi.";
        }
        
        // Benzersiz slug oluştur
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->makeUniqueSlug($data['title']);
        }

        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";
        $sql = "INSERT INTO news ($fields) VALUES ($values)";

        $ok = $this->conn->query($sql);
        if (!$ok) {
            $this->errorMessage = $this->conn->error;
        }
        return $ok;
    }

    public function getWarningMessage()
    {
        return $this->warningMessage ?? '';
    }

    /**
     * Update an existing news item; applies the same max-3 logic.
     */
    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);

        if (!empty($data['is_featured']) && $this->countFeatured($id) >= 3) {
            $this->errorMessage = "You can only have a maximum of 3 featured news items.";
            return false;
        }
        
        // Eğer başlık değiştirilmişse yeni slug oluştur
        if (!empty($data['title'])) {
            // Mevcut slugı kontrol et
            $result = $this->conn->query("SELECT slug FROM news WHERE id = $id");
            $current = $result->fetch_assoc();
            
            // Eğer başlıktan oluşturulan slug, mevcut slugtan farklıysa, yeni slug oluştur
            $basicSlug = $this->makeUniqueSlug($data['title']);
            if ($basicSlug != $current['slug']) {
                $data['slug'] = $basicSlug;
            }
        }

        $sets = [];
        foreach ($data as $k => $v) {
            $sets[] = "`{$k}` = '{$v}'";
        }
        $sql = "UPDATE news SET " . implode(', ', $sets) . " WHERE id = {$id}";
        return $this->conn->query($sql);
    }

    /**
     * Expose any error message to the controller.
     */
    public function getErrorMessage()
    {
        return $this->errorMessage ?? '';
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM news WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM news WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll($filters = [], $limit = null, $offset = null)
    {
        $where = [];
        $params = [];
        
        // Filtreleri uygula
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $where[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $sql = "SELECT * FROM news";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Varsayılan olarak en yeni haberleri getir
        $sql .= " ORDER BY created_at DESC";
        
        // Limit ve offset ekle
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }
        
        // Prepared statement kullan
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $news = [];
        while ($row = $result->fetch_assoc()) {
            // Görsel yolunu düzelt
            if (!empty($row['featured_image']) && !str_starts_with($row['featured_image'], 'http')) {
                $row['featured_image'] = $row['featured_image'];
            }
            
            // İçerikten özet oluştur
            if (empty($row['summary']) && !empty($row['content'])) {
                // HTML etiketlerini kaldır
                $content = strip_tags($row['content']);
                // Gereksiz boşlukları temizle
                $content = preg_replace('/\s+/', ' ', $content);
                // İlk 150 karakteri al ve son kelimeyi tamamla
                $summary = mb_substr($content, 0, 150, 'UTF-8');
                if (mb_strlen($content) > 150) {
                    // Son kelimeyi tamamla
                    $lastSpace = mb_strrpos($summary, ' ', 0, 'UTF-8');
                    if ($lastSpace !== false) {
                        $summary = mb_substr($summary, 0, $lastSpace, 'UTF-8');
                    }
                    $summary .= '...';
                }
                $row['summary'] = $summary;
            }
            
            $news[] = $row;
        }
        
        return $news;
    }

    public function getByCategory($category_id, $limit = null)
    {
        $category_id = (int) $category_id;
        $sql = "SELECT n.*, c.name as category 
                FROM news n
                JOIN news_category nc ON n.id = nc.news_id
                JOIN categories c ON nc.category_id = c.id
                WHERE nc.category_id = $category_id";
        
        if ($limit !== null) {
            $limit = (int) $limit;
            $sql .= " LIMIT $limit";
        }
        
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class Category extends Database
{
    public function create($data)
    {
        $data = $this->sanitize($data);
        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";

        $sql = "INSERT INTO categories ($fields) VALUES ($values)";
        return mysqli_query($this->conn, $sql);
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM categories WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM categories WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM categories";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function slugExists($slug)
    {
        $slug = $this->sanitize($slug);
        $sql = "SELECT COUNT(*) as count FROM categories WHERE slug = '$slug'";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] > 0;
    }
    
    public function getByName($name)
    {
        $name = $this->sanitize($name);
        $sql = "SELECT * FROM categories WHERE name = '$name'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }
}

// In your Section class
class Section
{
    private $conn;

    public function __construct()
    {
        require_once 'config.php'; // Your database configuration
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Create sections table
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS sections (
                    section_name VARCHAR(50) PRIMARY KEY,
                    news_id INT(11),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        if (!$this->conn->query($sql)) {
            die("Error creating table: " . $this->conn->error);
        }
    }

    // Get all current selections
    public function getAllGrouped()
    {
        $result = $this->conn->query("SELECT * FROM sections");
        $selections = [];

        while ($row = $result->fetch_assoc()) {
            if ($row['section_name'] === 'youtube_video') {
                $selections[$row['section_name']] = $row['youtube_url'];
            } else {
                $selections[$row['section_name']] = $row['news_id'];
            }
        }

        return $selections;
    }

    // Create or update a section
    public function updateOrCreate($sectionName, $data)
    {
        $news_id = isset($data['news_id']) ? $data['news_id'] : null;
        $youtube_url = isset($data['youtube_url']) ? $data['youtube_url'] : null;

        $stmt = $this->conn->prepare("INSERT INTO sections (section_name, news_id, youtube_url) 
                                    VALUES (?, ?, ?)
                                    ON DUPLICATE KEY UPDATE 
                                        news_id = VALUES(news_id),
                                        youtube_url = VALUES(youtube_url)");

        $stmt->bind_param("sis", $sectionName, $news_id, $youtube_url);
        return $stmt->execute();
    }
}

class Dosya extends Database
{
    protected $warningMessage;
    protected $errorMessage;

    protected function countFeatured($excludeId = null)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM dosya WHERE is_featured = 1";
        if ($excludeId !== null) {
            $excludeId = (int) $excludeId;
            $sql .= " AND id != {$excludeId}";
        }
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int) $row['cnt'];
    }

    public function create($data)
    {
        $data = $this->sanitize($data);

        // Demote to non-featured if max reached
        if (!empty($data['is_featured']) && $this->countFeatured() >= 3) {
            $data['is_featured'] = 0;
            $this->warningMessage = "Maksimum 3 öne çıkan dosyanız var; bu dosya öne çıkan olarak kaydedilmedi.";
        }

        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";
        $sql = "INSERT INTO dosya ($fields) VALUES ($values)";

        $ok = mysqli_query($this->conn, $sql);
        if (!$ok) {
            $this->errorMessage = mysqli_error($this->conn);
        }
        return $ok;
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);

        // Prevent featuring if max reached (excluding current item)
        if (!empty($data['is_featured']) && $this->countFeatured($id) >= 3) {
            $this->errorMessage = "You can only have a maximum of 3 featured files.";
            return false;
        }

        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "`{$key}` = '{$value}'"; // Added backticks for consistency
        }

        $sql = "UPDATE dosya SET " . implode(', ', $updates) . " WHERE id = $id";
        $ok = mysqli_query($this->conn, $sql);
        if (!$ok) {
            $this->errorMessage = mysqli_error($this->conn);
        }
        return $ok;
    }

    public function getWarningMessage()
    {
        return $this->warningMessage ?? '';
    }

    public function getErrorMessage()
    {
        return $this->errorMessage ?? '';
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM dosya WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM dosya WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM dosya";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class StoryMap extends Database
{
    protected $warningMessage;
    protected $errorMessage;

    protected function countFeatured($excludeId = null)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM storymaps WHERE is_featured = 1";
        if ($excludeId !== null) {
            $excludeId = (int) $excludeId;
            $sql .= " AND id != {$excludeId}";
        }
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int) $row['cnt'];
    }

    /**
     * Generate a URL-friendly, unique slug based on title.
     */
    protected function makeUniqueSlug(string $title): string
    {
        // 1) basic slugify
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' ', '', '–', '—'],
            ['i', 'g', 'u', 's', 'o', 'c', '-', '-', '-', '-'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // 2) ensure uniqueness by appending -2, -3, etc.
        $base = $slug;
        $i = 1;
        while (true) {
            $escaped = mysqli_real_escape_string($this->conn, $slug);
            $check = mysqli_query(
                $this->conn,
                "SELECT 1 FROM storymaps WHERE slug = '$escaped' LIMIT 1"
            );
            if (mysqli_num_rows($check) === 0) {
                break;
            }
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function create($data)
    {
        $data = $this->sanitize($data);
        
        // Demote to non-featured if max reached
        if (!empty($data['is_featured']) && $this->countFeatured() >= 3) {
            $data['is_featured'] = 0;
            $this->warningMessage = "Maksimum 3 öne çıkan StoryMap içeriğiniz var; bu içerik öne çıkan olarak kaydedilmedi.";
        }
        
        // Benzersiz slug oluştur
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->makeUniqueSlug($data['title']);
        }
        
        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";

        $sql = "INSERT INTO storymaps ($fields) VALUES ($values)";
        $ok = mysqli_query($this->conn, $sql);
        
        // Return the last inserted ID if successful
        if ($ok) {
            return mysqli_insert_id($this->conn);
        }
        
        $this->errorMessage = mysqli_error($this->conn);
        return false;
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);
        
        // Prevent featuring if max reached (excluding current item)
        if (!empty($data['is_featured']) && $this->countFeatured($id) >= 3) {
            $this->errorMessage = "En fazla 3 öne çıkan StoryMap içeriği olabilir.";
            return false;
        }
        
        // Eğer başlık değiştirilmişse yeni slug oluştur
        if (!empty($data['title'])) {
            // Mevcut slugı kontrol et
            $result = $this->conn->query("SELECT slug FROM storymaps WHERE id = $id");
            $current = $result->fetch_assoc();
            
            // Eğer başlıktan oluşturulan slug, mevcut slugtan farklıysa, yeni slug oluştur
            $basicSlug = $this->makeUniqueSlug($data['title']);
            if ($basicSlug != $current['slug']) {
                $data['slug'] = $basicSlug;
            }
        }
        
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE storymaps SET " . implode(', ', $updates) . " WHERE id = $id";
        $ok = mysqli_query($this->conn, $sql);
        if (!$ok) {
            $this->errorMessage = mysqli_error($this->conn);
        }
        return $ok;
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM storymaps WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM storymaps WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM storymaps";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getWarningMessage()
    {
        return $this->warningMessage ?? '';
    }

    public function getErrorMessage()
    {
        return $this->errorMessage ?? '';
    }
    
    public function linkCategories($storymap_id, $categories)
    {
        $storymap_id = (int) $storymap_id;
        
        // Önce mevcut kategorileri temizle
        $sql = "DELETE FROM storymaps_category WHERE storymaps_id = $storymap_id";
        mysqli_query($this->conn, $sql);
        
        // Yeni kategorileri ekle
        if (is_array($categories) && !empty($categories)) {
            $values = [];
            foreach ($categories as $cat_id) {
                $cat_id = (int) $cat_id;
                $values[] = "($storymap_id, $cat_id)";
            }
            
            if (!empty($values)) {
                $sql = "INSERT INTO storymaps_category (storymaps_id, category_id) VALUES " . implode(', ', $values);
                return mysqli_query($this->conn, $sql);
            }
        }
        
        return true;
    }
    
    public function getCategories($storymap_id)
    {
        $storymap_id = (int) $storymap_id;
        $sql = "SELECT c.* 
                FROM categories c
                JOIN storymaps_category sc ON c.id = sc.category_id
                WHERE sc.storymaps_id = $storymap_id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class Timeline extends Database
{
    /**
     * Generate a URL-friendly, unique slug based on $title.
     */
    protected function makeUniqueSlug(string $title): string
    {
        // 1) basic slugify
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' ‘', '’', '–', '—'],
            ['i', 'g', 'u', 's', 'o', 'c', '-', '-', '-', '-'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // 2) ensure uniqueness by appending -2, -3, etc.
        $base = $slug;
        $i = 1;
        while (true) {
            $escaped = mysqli_real_escape_string($this->conn, $slug);
            $check = mysqli_query(
                $this->conn,
                "SELECT 1 FROM timeline WHERE slug = '$escaped' LIMIT 1"
            );
            if (mysqli_num_rows($check) === 0) {
                break;
            }
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function create(array $data)
    {
        $data = $this->sanitize($data);

        // auto-generate slug from title
        if (empty($data['title'])) {
            throw new InvalidArgumentException("Title is required for slug generation");
        }
        $data['slug'] = $this->makeUniqueSlug($data['title']);

        // build INSERT
        $fields = implode(', ', array_keys($data));
        $escapedValues = array_map(function ($v) {
            return mysqli_real_escape_string($this->conn, $v);
        }, array_values($data));
        $values = "'" . implode("','", $escapedValues) . "'";

        $sql = "INSERT INTO timeline ($fields) VALUES ($values)";
        return mysqli_query($this->conn, $sql);
    }

    public function update(int $id, array $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);

        // if title changed, regenerate slug
        if (isset($data['title']) && $data['title'] !== '') {
            $data['slug'] = $this->makeUniqueSlug($data['title']);
        }

        // build UPDATE
        $sets = [];
        foreach ($data as $key => $value) {
            $escapedValue = mysqli_real_escape_string($this->conn, $value);
            $sets[] = "`$key` = '$escapedValue'";
        }

        $sql = "UPDATE timeline SET " . implode(', ', $sets) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete(int $id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM timeline WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById(int $id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM timeline WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM timeline ORDER BY new_date DESC, created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class FourNokta extends Database
{
    public function create($data)
    {
        $data = $this->sanitize($data);
        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";

        $sql = "INSERT INTO 4nokta1 ($fields) VALUES ($values)";
        return mysqli_query($this->conn, $sql);
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE 4nokta1 SET " . implode(', ', $updates) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM 4nokta1 WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM 4nokta1 WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM 4nokta1";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class Portre extends Database
{
    protected $errorMessage;

    public function create($data)
    {
        try {
        $data = $this->sanitize($data);
        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";

        $sql = "INSERT INTO portre ($fields) VALUES ($values)";
            $result = mysqli_query($this->conn, $sql);
            
            if (!$result) {
                $this->errorMessage = "Veritabanı hatası: " . mysqli_error($this->conn);
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->errorMessage = "Hata: " . $e->getMessage();
            return false;
        }
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE portre SET " . implode(', ', $updates) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM portre WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM portre WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM portre ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getLatest()
    {
        $sql = "SELECT * FROM portre ORDER BY created_at DESC LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getErrorMessage()
    {
        return $this->errorMessage ?? 'Bilinmeyen bir hata oluştu.';
    }
}

class Social extends Database
{
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS social (
            id INT(11) NOT NULL AUTO_INCREMENT,
            platform VARCHAR(100) NOT NULL,
            social_url VARCHAR(255) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (platform)
        )";
        return mysqli_query($this->conn, $sql);
    }

    public function create($platform, $social_url)
    {
        $platform = $this->sanitize($platform);
        $social_url = $this->sanitize($social_url);

        $sql = "INSERT INTO social (platform, social_url) 
                VALUES ('$platform', '$social_url')";
        return mysqli_query($this->conn, $sql);
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);
        $updates = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }

        $sql = "UPDATE social SET " . implode(', ', $updates) . " WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM social WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM social WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM social";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class Hafiza extends Database
{
    protected $warningMessage;
    protected $errorMessage;

    protected function countFeatured($excludeId = null)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM hafiza WHERE is_featured = 1";
        if ($excludeId !== null) {
            $excludeId = (int) $excludeId;
            $sql .= " AND id != {$excludeId}";
        }
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int) $row['cnt'];
    }
    
    /**
     * Generate a URL-friendly, unique slug based on title.
     */
    protected function makeUniqueSlug(string $title): string
    {
        // 1) basic slugify
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' ', '', '–', '—'],
            ['i', 'g', 'u', 's', 'o', 'c', '-', '-', '-', '-'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // 2) ensure uniqueness by appending -2, -3, etc.
        $base = $slug;
        $i = 1;
        while (true) {
            $escaped = mysqli_real_escape_string($this->conn, $slug);
            $check = mysqli_query(
                $this->conn,
                "SELECT 1 FROM hafiza WHERE slug = '$escaped' LIMIT 1"
            );
            if (mysqli_num_rows($check) === 0) {
                break;
            }
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function create($data)
    {
        $data = $this->sanitize($data);

        // Demote to non-featured if max reached
        if (!empty($data['is_featured']) && $this->countFeatured() >= 3) {
            $data['is_featured'] = 0;
            $this->warningMessage = "Maksimum 3 öne çıkan hafıza içeriğiniz var; bu içerik öne çıkan olarak kaydedilmedi.";
        }
        
        // Benzersiz slug oluştur
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->makeUniqueSlug($data['title']);
        }

        $fields = implode(', ', array_keys($data));
        $values = "'" . implode("', '", $data) . "'";
        $sql = "INSERT INTO hafiza ($fields) VALUES ($values)";

        $ok = mysqli_query($this->conn, $sql);
        if (!$ok) {
            $this->errorMessage = mysqli_error($this->conn);
        }
        return $ok;
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        $data = $this->sanitize($data);

        // Prevent featuring if max reached (excluding current item)
        if (!empty($data['is_featured']) && $this->countFeatured($id) >= 3) {
            $this->errorMessage = "En fazla 3 öne çıkan hafıza içeriği olabilir.";
            return false;
        }
        
        // Eğer başlık değiştirilmişse yeni slug oluştur
        if (!empty($data['title'])) {
            // Mevcut slugı kontrol et
            $result = $this->conn->query("SELECT slug FROM hafiza WHERE id = $id");
            $current = $result->fetch_assoc();
            
            // Eğer başlıktan oluşturulan slug, mevcut slugtan farklıysa, yeni slug oluştur
            $basicSlug = $this->makeUniqueSlug($data['title']);
            if ($basicSlug != $current['slug']) {
                $data['slug'] = $basicSlug;
            }
        }

        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "`{$key}` = '{$value}'";
        }

        $sql = "UPDATE hafiza SET " . implode(', ', $updates) . " WHERE id = $id";
        $ok = mysqli_query($this->conn, $sql);
        if (!$ok) {
            $this->errorMessage = mysqli_error($this->conn);
        }
        return $ok;
    }

    public function getWarningMessage()
    {
        return $this->warningMessage ?? '';
    }

    public function getErrorMessage()
    {
        return $this->errorMessage ?? '';
    }

    public function delete($id)
    {
        $id = (int) $id;
        $sql = "DELETE FROM hafiza WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $sql = "SELECT * FROM hafiza WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function getByCategory($category_id, $limit = null)
    {
        $category_id = (int) $category_id;
        $sql = "SELECT h.*, c.name as category 
                FROM hafiza h
                JOIN hafiza_category hc ON h.id = hc.hafiza_id
                JOIN categories c ON hc.category_id = c.id
                WHERE hc.category_id = $category_id";
        
        if ($limit !== null) {
            $limit = (int) $limit;
            $sql .= " LIMIT $limit";
        }
        
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM hafiza ORDER BY is_featured DESC, new_date DESC, created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function linkCategories($hafiza_id, $categories)
    {
        $hafiza_id = (int) $hafiza_id;
        
        // Önce mevcut kategorileri temizle
        $sql = "DELETE FROM hafiza_category WHERE hafiza_id = $hafiza_id";
        mysqli_query($this->conn, $sql);
        
        // Yeni kategorileri ekle
        if (is_array($categories) && !empty($categories)) {
            $values = [];
            foreach ($categories as $cat_id) {
                $cat_id = (int) $cat_id;
                $values[] = "($hafiza_id, $cat_id)";
            }
            
            if (!empty($values)) {
                $sql = "INSERT INTO hafiza_category (hafiza_id, category_id) VALUES " . implode(', ', $values);
                return mysqli_query($this->conn, $sql);
            }
        }
        
        return true;
    }
    
    public function getCategories($hafiza_id)
    {
        $hafiza_id = (int) $hafiza_id;
        $sql = "SELECT c.* 
                FROM categories c
                JOIN hafiza_category hc ON c.id = hc.category_id
                WHERE hc.hafiza_id = $hafiza_id";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
?>