<?php
// Show all errors except warnings, notices, and deprecated
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>

<?php
/*
 * IsnadExplorerEdu - Single-file Educational Tool for Hadith Isnad Exploration
 * SQLite-based application with responsive UI for Urdu/Pashto users
 * Supports admin management of Hadith and narrator data
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// App Configuration
$cfg = [
    'dbfile' => 'isnad_explorer.db',
    'csv' => 'all_rawis.csv',
    'salt' => 'IsnadExplorerEdu@2025',
    'admin' => [
        'user' => 'admin',
        'pass' => 'admin123' // Default password - change after first login
    ]
];

// Database Initialization
$db = new PDO('sqlite:' . $cfg['dbfile']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("PRAGMA foreign_keys = ON");

// Create tables if they don't exist
function initDB($db, $cfg) {
    $schema = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        is_admin INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS narrators (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        scholar_indx INTEGER,
        name TEXT NOT NULL,
        grade TEXT,
        birth_date TEXT,
        death_date TEXT,
        bio TEXT,
        teachers TEXT,
        students TEXT,
        tags TEXT
    );
    
    CREATE TABLE IF NOT EXISTS hadiths (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        text TEXT NOT NULL,
        source TEXT,
        isnad_chain TEXT NOT NULL,
        added_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(added_by) REFERENCES users(id)
    );";
    
    $db->exec($schema);
    
    // Add default admin if not exists
    $q = $db->prepare("SELECT id FROM users WHERE username = :u");
    $q->execute([':u' => $cfg['admin']['user']]);
    
    if (!$q->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (username, password, is_admin) VALUES (:u, :p, 1)");
        $stmt->execute([
            ':u' => $cfg['admin']['user'],
            ':p' => password_hash($cfg['admin']['pass'] . $cfg['salt'], PASSWORD_BCRYPT)
        ]);
    }
    
    // Import CSV data if narrators table is empty
    $q = $db->query("SELECT COUNT(*) as cnt FROM narrators");
    if ($q->fetch(PDO::FETCH_ASSOC)['cnt'] == 0 && file_exists($cfg['csv'])) {
        importNarrators($db, $cfg['csv']);
    }
}

// Import narrator data from CSV
// Import narrator data from CSV
function importNarrators($db, $csvFile) {
    if (($h = fopen($csvFile, "r")) !== FALSE) {
        // Get header
        $header = fgetcsv($h, 0, ",");
        
        // Begin transaction for faster insertion
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO narrators (scholar_indx, name, grade, birth_date, death_date, bio, teachers, students, tags) 
                              VALUES (:idx, :name, :grade, :birth, :death, :bio, :teachers, :students, :tags)");
        
        while (($data = fgetcsv($h, 0, ",")) !== FALSE) {
            if (count($data) < 10) continue; // Skip invalid rows
            
            $bio = "مقام پیدائش: " . ($data[17] ?? 'نامعلوم') . 
                  " - تاریخ پیدائش: " . ($data[18] ?? 'نامعلوم') . 
                  " - تاریخ وفات: " . ($data[21] ?? 'نامعلوم') . 
                  " - مقام وفات: " . ($data[23] ?? 'نامعلوم');
            
            $stmt->execute([
                ':idx' => $data[0] ?? null,
                ':name' => $data[1] ?? '',
                ':grade' => $data[2] ?? '',
                ':birth' => $data[18] ?? '',
                ':death' => $data[21] ?? '',
                ':bio' => $bio,
                ':teachers' => $data[16] ?? '',
                ':students' => $data[15] ?? '',
                ':tags' => $data[13] ?? ''
            ]);
        }
        
        $db->commit();
        fclose($h);
    }
}

// Initialize database
try {
    initDB($db, $cfg);
} catch (PDOException $e) {
    die("Database initialization error: " . $e->getMessage());
}

// Authentication Functions
function login($db, $username, $password, $salt) {
    $stmt = $db->prepare("SELECT id, password, is_admin FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password . $salt, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $user['is_admin'];
        return true;
    }
    return false;
}

function register($db, $username, $password, $salt) {
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:u, :p)");
        $stmt->execute([
            ':u' => $username,
            ':p' => password_hash($password . $salt, PASSWORD_BCRYPT)
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function logout() {
    session_unset();
    session_destroy();
}

// Hadith and Narrator Functions
function getHadiths($db, $limit = 10, $offset = 0) {
    $stmt = $db->prepare("SELECT id, title, text, source, created_at FROM hadiths ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHadith($db, $id) {
    $stmt = $db->prepare("SELECT * FROM hadiths WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveHadith($db, $title, $text, $source, $isnad_chain, $user_id) {
    $stmt = $db->prepare("INSERT INTO hadiths (title, text, source, isnad_chain, added_by) 
                         VALUES (:title, :text, :source, :isnad, :user)");
    $stmt->execute([
        ':title' => $title,
        ':text' => $text,
        ':source' => $source,
        ':isnad' => $isnad_chain,
        ':user' => $user_id
    ]);
    return $db->lastInsertId();
}

function updateHadith($db, $id, $title, $text, $source, $isnad_chain) {
    $stmt = $db->prepare("UPDATE hadiths SET title = :title, text = :text, 
                         source = :source, isnad_chain = :isnad WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':title' => $title,
        ':text' => $text,
        ':source' => $source,
        ':isnad' => $isnad_chain
    ]);
    return $stmt->rowCount() > 0;
}

function deleteHadith($db, $id) {
    $stmt = $db->prepare("DELETE FROM hadiths WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

function getNarrator($db, $id) {
    $stmt = $db->prepare("SELECT * FROM narrators WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getNarratorByScholarIdx($db, $idx) {
    $stmt = $db->prepare("SELECT * FROM narrators WHERE scholar_indx = :idx");
    $stmt->execute([':idx' => $idx]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getNarrators($db, $limit = 20, $offset = 0, $search = '') {
    $sql = "SELECT id, scholar_indx, name, grade FROM narrators";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " WHERE name LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY scholar_indx LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateNarrator($db, $id, $data) {
    $stmt = $db->prepare("UPDATE narrators SET name = :name, grade = :grade, 
                         birth_date = :birth, death_date = :death, bio = :bio,
                         teachers = :teachers, students = :students, tags = :tags
                         WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':name' => $data['name'],
        ':grade' => $data['grade'],
        ':birth' => $data['birth_date'],
        ':death' => $data['death_date'],
        ':bio' => $data['bio'],
        ':teachers' => $data['teachers'],
        ':students' => $data['students'],
        ':tags' => $data['tags']
    ]);
    return $stmt->rowCount() > 0;
}

function getIsnadChain($db, $hadithId) {
    $hadith = getHadith($db, $hadithId);
    if (!$hadith) return [];
    
    $chain = [];
    $isnadIds = explode(',', $hadith['isnad_chain']);
    
    foreach ($isnadIds as $idx) {
        $narrator = getNarratorByScholarIdx($db, trim($idx));
        if ($narrator) {
            $chain[] = $narrator;
        }
    }
    
    return $chain;
}

// Handle form submissions
$msg = '';
$action = $_GET['a'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'login':
            if (isset($_POST['username'], $_POST['password'])) {
                if (login($db, $_POST['username'], $_POST['password'], $cfg['salt'])) {
                    header('Location: ?a=home');
                    exit;
                } else {
                    $msg = 'غلط صارف نام یا پاس ورڈ';
                }
            }
            break;
            
        case 'register':
            if (isset($_POST['username'], $_POST['password'], $_POST['password2'])) {
                if ($_POST['password'] !== $_POST['password2']) {
                    $msg = 'پاس ورڈ مماثل نہیں ہیں';
                } else if (strlen($_POST['password']) < 6) {
                    $msg = 'پاس ورڈ کم از کم 6 حروف کا ہونا چاہیے';
                } else {
                    $user_id = register($db, $_POST['username'], $_POST['password'], $cfg['salt']);
                    if ($user_id) {
                        login($db, $_POST['username'], $_POST['password'], $cfg['salt']);
                        header('Location: ?a=home');
                        exit;
                    } else {
                        $msg = 'صارف نام پہلے سے موجود ہے';
                    }
                }
            }
            break;
            
        case 'add_hadith':
            if (isAdmin() && isset($_POST['title'], $_POST['text'], $_POST['source'], $_POST['isnad_chain'])) {
                $id = saveHadith(
                    $db,
                    $_POST['title'],
                    $_POST['text'],
                    $_POST['source'],
                    $_POST['isnad_chain'],
                    $_SESSION['user_id']
                );
                if ($id) {
                    header('Location: ?a=hadith&id=' . $id);
                    exit;
                } else {
                    $msg = 'حدیث محفوظ نہیں ہو سکی';
                }
            }
            break;
            
        case 'edit_hadith':
            if (isAdmin() && isset($_POST['id'], $_POST['title'], $_POST['text'], $_POST['source'], $_POST['isnad_chain'])) {
                if (updateHadith($db, $_POST['id'], $_POST['title'], $_POST['text'], $_POST['source'], $_POST['isnad_chain'])) {
                    header('Location: ?a=hadith&id=' . $_POST['id']);
                    exit;
                } else {
                    $msg = 'حدیث کی تازہ کاری نہیں ہو سکی';
                }
            }
            break;
            
        case 'delete_hadith':
            if (isAdmin() && isset($_POST['id'])) {
                if (deleteHadith($db, $_POST['id'])) {
                    header('Location: ?a=hadiths');
                    exit;
                } else {
                    $msg = 'حدیث حذف نہیں ہو سکی';
                }
            }
            break;
            
        case 'edit_narrator':
            if (isAdmin() && isset($_POST['id'])) {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'grade' => $_POST['grade'] ?? '',
                    'birth_date' => $_POST['birth_date'] ?? '',
                    'death_date' => $_POST['death_date'] ?? '',
                    'bio' => $_POST['bio'] ?? '',
                    'teachers' => $_POST['teachers'] ?? '',
                    'students' => $_POST['students'] ?? '',
                    'tags' => $_POST['tags'] ?? ''
                ];
                
                if (updateNarrator($db, $_POST['id'], $data)) {
                    header('Location: ?a=narrator&id=' . $_POST['id']);
                    exit;
                } else {
                    $msg = 'راوی کی معلومات تازہ نہیں ہو سکیں';
                }
            }
            break;
            
        case 'logout':
            logout();
            header('Location: ?a=login');
            exit;
            break;
    }
}

// HTML Output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>

<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اسناد ایکسپلورر ایجوکیشنل ٹول</title>
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #4a5568;
            --accent: #38a169;
            --light: #f7fafc;
            --dark: #2d3748;
            --danger: #e53e3e;
            --success: #48bb78;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', Arial, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--dark);
            line-height: 1.6;
            font-size: 16px;
            direction: rtl;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), #2c5282);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 1.8rem;
            margin: 0;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav li {
            margin-left: 1.5rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            position: relative;
        }
        
        nav a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            right: 0;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        nav a:hover:after {
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        input[type=text], input[type=password], textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
            direction: rtl;
        }
        
        textarea {
            min-height: 150px;
        }
        
        button, .btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        button:hover, .btn:hover {
            background-color: #2c5282;
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #38a169;
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .btn-danger:hover {
            background-color: #c53030;
        }
        
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            background-color: #fed7d7;
            color: #c53030;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .hadith-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .hadith-card:hover {
            transform: translateY(-5px);
        }
        
        .hadith-header {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
        }
        
        .hadith-body {
            padding: 1rem;
        }
        
        .hadith-text {
            margin-bottom: 1rem;
            font-style: italic;
        }
        
        .hadith-source {
            color: var(--secondary);
            font-size: 0.875rem;
        }
        
        .isnad-chain {
            margin-top: 2rem;
        }
        
        .isnad-chain h3 {
            margin-bottom: 1rem;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }
        
        .isnad-chain ul {
            list-style: none;
        }
        
        .isnad-chain li {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
        }
        
        .isnad-chain li:last-child {
            border-bottom: none;
        }
        
        .isnad-tree {
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        .tree-node {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--light);
            margin-bottom: 1rem;
            position: relative;
        }
        
        .tree-node::before {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            height: 20px;
            border-left: 2px dashed var(--secondary);
        }
        
        .tree-node:last-child::before {
            display: none;
        }
        
        .narrator-bio {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .narrator-name {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }
        
        .narrator-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--secondary);
        }
        
        .pagination {
            display: flex;
            list-style: none;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination li {
            margin: 0 0.25rem;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            text-decoration: none;
            color: var(--primary);
            background-color: white;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
        }
        
        .search-box {
            margin-bottom: 1.5rem;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }
        
        .disclaimer {
            background-color: #fdfdea;
            border: 1px solid #fefcbf;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #744210;
        }
        
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 1rem;
                justify-content: center;
            }
            
            nav li {
                margin: 0 0.75rem;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
        }
		h2 {
    padding-bottom: 28px;
    text-align: center;
    background: #c0cee9;
    padding-top: 15px;
    margin-bottom: 9px;
}
h3 {
    margin-bottom: 12px;
}
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>اسناد ایکسپلورر ایجوکیشنل ٹول</h1>
            <nav>
                <ul>
                    <li><a href="?a=home">گھر</a></li>
                    <li><a href="?a=hadiths">احادیث</a></li>
                    <li><a href="?a=narrators">راوی</a></li>
                    <?php if(isAdmin()): ?>
                    <li><a href="?a=add_hadith">حدیث شامل کریں</a></li>
                    <?php endif; ?>
                    <?php if(isLoggedIn()): ?>
                    <li><a href="?a=logout">لاگ آوٹ</a></li>
                    <?php else: ?>
                    <li><a href="?a=login">لاگ ان</a></li>
                    <li><a href="?a=register">رجسٹر</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <?php if(!empty($msg)): ?>
        <div class="message"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        
        <?php
        // Router
        switch ($action) {
            case 'login':
                ?>
                <div class="card">
                    <h2>لاگ ان</h2>
                    <form method="post" action="?a=login">
                        <div class="form-group">
                            <label for="username">صارف نام</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">پاس ورڈ</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit">لاگ ان</button>
                    </form>
                </div>
                <?php
                break;
                
            case 'register':
                ?>
                <div class="card">
                    <h2>رجسٹر</h2>
                    <form method="post" action="?a=register">
                        <div class="form-group">
                            <label for="username">صارف نام</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">پاس ورڈ</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password2">پاس ورڈ دوبارہ</label>
                            <input type="password" id="password2" name="password2" required>
                        </div>
                        <button type="submit">رجسٹر</button>
                    </form>
                </div>
                <?php
                break;
                
            case 'hadiths':
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;
                $hadiths = getHadiths($db, $limit, $offset);
                ?>
                <h2>احادیث</h2>
                <div class="disclaimer">
                    <strong>تنبیہ:</strong> یہ ایک سادہ تعلیمی ٹول ہے جو اسناد سیکھنے کے لیے بنایا گیا ہے۔ حدیث کے صحیح مصادر اور علماء سے استفادہ کریں۔
                </div>
                
                <div class="grid">
                    <?php foreach($hadiths as $hadith): ?>
                    <div class="hadith-card">
                        <div class="hadith-header">
                            <h3><?= htmlspecialchars($hadith['title']) ?></h3>
                        </div>
                        <div class="hadith-body">
                            <p class="hadith-text"><?= nl2br(htmlspecialchars(substr($hadith['text'], 0, 150))) ?>...</p>
                            <p class="hadith-source"><?= htmlspecialchars($hadith['source']) ?></p>
                            <a href="?a=hadith&id=<?= $hadith['id'] ?>" class="btn">مکمل دیکھیں</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <ul class="pagination">
                    <?php if($page > 1): ?>
                    <li><a href="?a=hadiths&page=<?= $page-1 ?>">پیچھے</a></li>
                    <?php endif; ?>
                    <li><a href="?a=hadiths&page=<?= $page ?>" class="active"><?= $page ?></a></li>
                    <li><a href="?a=hadiths&page=<?= $page+1 ?>">آگے</a></li>
                </ul>
                <?php
                break;
                
            case 'hadith':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                $hadith = getHadith($db, $id);
                
                if (!$hadith) {
                    echo '<div class="message">حدیث نہیں ملی</div>';
                    break;
                }
                
                $isnadChain = getIsnadChain($db, $id);
                ?>
                <div class="card">
                    <h2><?= htmlspecialchars($hadith['title']) ?></h2>
                    <div class="hadith-text">
                        <p><?= nl2br(htmlspecialchars($hadith['text'])) ?></p>
                    </div>
                    <div class="hadith-source">
                        <p><strong>ماخذ:</strong> <?= htmlspecialchars($hadith['source']) ?></p>
                    </div>
                    
                    <?php if(isAdmin()): ?>
                    <div style="margin-top: 1rem;">
                        <a href="?a=edit_hadith&id=<?= $hadith['id'] ?>" class="btn">تدوین کریں</a>
                        <form method="post" action="?a=delete_hadith" style="display: inline-block;">
                            <input type="hidden" name="id" value="<?= $hadith['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('کیا آپ واقعی یہ حدیث حذف کرنا چاہتے ہیں؟')">حذف کریں</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="isnad-chain">
                    <h3>اسناد</h3>
                    <ul>
                        <?php foreach($isnadChain as $narrator): ?>
                        <li>
                            <span><?= htmlspecialchars($narrator['name']) ?></span>
                            <a href="?a=narrator&id=<?= $narrator['id'] ?>" class="btn">تفصیلات دیکھیں</a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="isnad-tree">
                    <h3>اسناد ٹری</h3>
                    <?php foreach($isnadChain as $narrator): ?>
                    <div class="tree-node">
                        <strong><?= htmlspecialchars($narrator['name']) ?></strong>
                        <div><?= htmlspecialchars($narrator['grade']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php
                break;
                
            case 'narrator':
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                $narrator = getNarrator($db, $id);
                
                if (!$narrator) {
                    echo '<div class="message">راوی نہیں ملے</div>';
                    break;
                }
                
                $teachers = [];
                if (!empty($narrator['teachers'])) {
                    $teacherIds = explode(',', $narrator['teachers']);
                    foreach ($teacherIds as $tid) {
                        $teacher = getNarratorByScholarIdx($db, trim($tid));
                        if ($teacher) {
                            $teachers[] = $teacher;
                        }
                    }
                }
                
                $students = [];
                if (!empty($narrator['students'])) {
                    $studentIds = explode(',', $narrator['students']);
                    foreach ($studentIds as $sid) {
                        $student = getNarratorByScholarIdx($db, trim($sid));
                        if ($student) {
                            $students[] = $student;
                        }
                    }
                }
                ?>
                <div class="card">
                    <h2><?= htmlspecialchars($narrator['name']) ?></h2>
                    
                    <div class="narrator-details">
                        <div class="detail-group">
                            <span class="detail-label">درجہ:</span>
                            <span><?= htmlspecialchars($narrator['grade']) ?></span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">تاریخ پیدائش:</span>
                            <span><?= htmlspecialchars($narrator['birth_date']) ?></span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">تاریخ وفات:</span>
                            <span><?= htmlspecialchars($narrator['death_date']) ?></span>
                        </div>
                    </div>
                    
                    <div class="narrator-bio">
                        <h3>مختصر تعارف</h3>
                        <p><?= nl2br(htmlspecialchars($narrator['bio'])) ?></p>
                    </div>
                    
                    <?php if(!empty($teachers)): ?>
                    <div class="isnad-chain">
                        <h3>اساتذہ</h3>
                        <ul>
                            <?php foreach($teachers as $teacher): ?>
                            <li>
                                <span><?= htmlspecialchars($teacher['name']) ?></span>
                                <a href="?a=narrator&id=<?= $teacher['id'] ?>" class="btn">تفصیلات دیکھیں</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($students)): ?>
                    <div class="isnad-chain">
                        <h3>شاگرد</h3>
                        <ul>
                            <?php foreach($students as $student): ?>
                            <li>
                                <span><?= htmlspecialchars($student['name']) ?></span>
                                <a href="?a=narrator&id=<?= $student['id'] ?>" class="btn">تفصیلات دیکھیں</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isAdmin()): ?>
                    <div style="margin-top: 1rem;">
                        <a href="?a=edit_narrator&id=<?= $narrator['id'] ?>" class="btn">تدوین کریں</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
                break;
                
            case 'narrators':
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = 20;
                $offset = ($page - 1) * $limit;
                $search = $_GET['search'] ?? '';
                
                $narrators = getNarrators($db, $limit, $offset, $search);
                ?>
                <h2>راویان حدیث</h2>
                
                <div class="search-box">
                    <form method="get" action="">
                        <input type="hidden" name="a" value="narrators">
                        <input type="text" name="search" placeholder="نام سے تلاش کریں..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit">تلاش</button>
                    </form>
                </div>
                
                <div class="grid">
                    <?php foreach($narrators as $narrator): ?>
                    <div class="hadith-card">
                        <div class="hadith-header">
                            <h3><?= htmlspecialchars($narrator['name']) ?></h3>
                        </div>
                        <div class="hadith-body">
                            <p><strong>درجہ:</strong> <?= htmlspecialchars($narrator['grade']) ?></p>
                            <a href="?a=narrator&id=<?= $narrator['id'] ?>" class="btn">تفصیلات دیکھیں</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <ul class="pagination">
                    <?php if($page > 1): ?>
                    <li><a href="?a=narrators&page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">پیچھے</a></li>
                    <?php endif; ?>
                    <li><a href="?a=narrators&page=<?= $page ?>&search=<?= urlencode($search) ?>" class="active"><?= $page ?></a></li>
                    <li><a href="?a=narrators&page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">آگے</a></li>
                </ul>
                <?php
                break;
                
            case 'add_hadith':
                if (!isAdmin()) {
                    echo '<div class="message">آپ کو یہ صفحہ دیکھنے کی اجازت نہیں ہے</div>';
                    break;
                }
                ?>
                <div class="card">
                    <h2>نئی حدیث شامل کریں</h2>
                    <form method="post" action="?a=add_hadith">
                        <div class="form-group">
                            <label for="title">عنوان</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="text">متن حدیث</label>
                            <textarea id="text" name="text" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="source">ماخذ</label>
                            <input type="text" id="source" name="source" required>
                        </div>
                        <div class="form-group">
                            <label for="isnad_chain">اسناد چین (کوما سے الگ کریں، راوی کے اشاریے لکھیں)</label>
                            <input type="text" id="isnad_chain" name="isnad_chain" required placeholder="1, 2, 3, 19">
                        </div>
                        <button type="submit">محفوظ کریں</button>
                    </form>
                </div>
                <?php
                break;
                
            case 'edit_hadith':
                if (!isAdmin()) {
                    echo '<div class="message">آپ کو یہ صفحہ دیکھنے کی اجازت نہیں ہے</div>';
                    break;
                }
                
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                $hadith = getHadith($db, $id);
                
                if (!$hadith) {
                    echo '<div class="message">حدیث نہیں ملی</div>';
                    break;
                }
                ?>
                <div class="card">
                    <h2>حدیث میں تدوین کریں</h2>
                    <form method="post" action="?a=edit_hadith">
                        <input type="hidden" name="id" value="<?= $hadith['id'] ?>">
                        <div class="form-group">
                            <label for="title">عنوان</label>
                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($hadith['title']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="text">متن حدیث</label>
                            <textarea id="text" name="text" required><?= htmlspecialchars($hadith['text']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="source">ماخذ</label>
                            <input type="text" id="source" name="source" value="<?= htmlspecialchars($hadith['source']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="isnad_chain">اسناد چین (کوما سے الگ کریں، راوی کے اشاریے لکھیں)</label>
                            <input type="text" id="isnad_chain" name="isnad_chain" value="<?= htmlspecialchars($hadith['isnad_chain']) ?>" required>
                        </div>
                        <button type="submit">تازہ کریں</button>
                    </form>
                </div>
                <?php
                break;
                
            case 'edit_narrator':
                if (!isAdmin()) {
                    echo '<div class="message">آپ کو یہ صفحہ دیکھنے کی اجازت نہیں ہے</div>';
                    break;
                }
                
                $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                $narrator = getNarrator($db, $id);
                
                if (!$narrator) {
                    echo '<div class="message">راوی نہیں ملے</div>';
                    break;
                }
                ?>
                <div class="card">
                    <h2>راوی میں تدوین کریں</h2>
                    <form method="post" action="?a=edit_narrator">
                        <input type="hidden" name="id" value="<?= $narrator['id'] ?>">
                        <div class="form-group">
                            <label for="name">نام</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($narrator['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="grade">درجہ</label>
                            <input type="text" id="grade" name="grade" value="<?= htmlspecialchars($narrator['grade']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="birth_date">تاریخ پیدائش</label>
                            <input type="text" id="birth_date" name="birth_date" value="<?= htmlspecialchars($narrator['birth_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="death_date">تاریخ وفات</label>
                            <input type="text" id="death_date" name="death_date" value="<?= htmlspecialchars($narrator['death_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="bio">مختصر تعارف</label>
                            <textarea id="bio" name="bio"><?= htmlspecialchars($narrator['bio']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="teachers">اساتذہ (کوما سے الگ کریں، اشاریے لکھیں)</label>
                            <input type="text" id="teachers" name="teachers" value="<?= htmlspecialchars($narrator['teachers']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="students">شاگرد (کوما سے الگ کریں، اشاریے لکھیں)</label>
                            <input type="text" id="students" name="students" value="<?= htmlspecialchars($narrator['students']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="tags">ٹیگز</label>
                            <input type="text" id="tags" name="tags" value="<?= htmlspecialchars($narrator['tags']) ?>">
                        </div>
                        <button type="submit">تازہ کریں</button>
                    </form>
                </div>
                <?php
                break;
                
            case 'home':
            default:
                ?>
                <div class="card">
                    <h2>اسلام میں حدیث اور اسناد کا مقام</h2>
                    <p>اسناد ایک اہم اسلامی نظام ہے جو ہمیں حضور پاک صلی اللہ علیہ وسلم کی احادیث کی صحت کی جانچ کی اجازت دیتا ہے۔ یہ راویوں کا ایک سلسلہ ہے جو حدیث کو ایک نسل سے دوسری نسل تک منتقل کرتے ہیں۔</p>
                    <p>اس ٹول کا مقصد راویوں کے سلسلے کو آسانی سے سمجھنے میں مدد کرنا ہے۔ آپ یہاں صحیح احادیث کے اسناد کو دیکھ سکتے ہیں اور راویوں کے بارے میں مزید جان سکتے ہیں۔</p>
                </div>
                
                <div class="disclaimer">
                    <strong>تنبیہ:</strong> یہ ایک سادہ تعلیمی ٹول ہے جو اسناد سیکھنے کے لیے بنایا گیا ہے۔ اس میں دکھائے گئے اسناد مکمل نہیں ہو سکتے۔ حدیث کے صحیح مصادر اور علماء سے استفادہ کریں۔
                </div>
                
                <h2>حالیہ احادیث</h2>
                <div class="grid">
                    <?php 
                    $recent_hadiths = getHadiths($db, 6, 0);
                    foreach($recent_hadiths as $hadith): 
                    ?>
                    <div class="hadith-card">
                        <div class="hadith-header">
                            <h3><?= htmlspecialchars($hadith['title']) ?></h3>
                        </div>
                        <div class="hadith-body">
                            <p class="hadith-text"><?= nl2br(htmlspecialchars(substr($hadith['text'], 0, 150))) ?>...</p>
                            <p class="hadith-source"><?= htmlspecialchars($hadith['source']) ?></p>
                            <a href="?a=hadith&id=<?= $hadith['id'] ?>" class="btn">مکمل دیکھیں</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="?a=hadiths" class="btn">تمام احادیث دیکھیں</a>
                </div>
                <?php
                break;
        }
        ?>
    </main>
    
    <footer>
        <div class="container">
            <p>اسناد ایکسپلورر ایجوکیشنل ٹول &copy; ٢٠٢٥</p>
            <p>یہ ایک تعلیمی مقاصد کے لیے بنایا گیا ٹول ہے</p>
        </div>
    </footer>

    <script>
        // Simple client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let valid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            valid = false;
                            field.style.borderColor = '#e53e3e';
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('براہ کرم تمام ضروری فیلڈز پُر کریں');
                    }
                });
            });
        });
    </script>
</body>
</html>
<script>
const fontUrl = "https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&display=swap";

const link = document.createElement("link");
link.rel = "stylesheet";
link.href = fontUrl;
document.head.appendChild(link);

const style = document.createElement("style");
style.innerHTML = `
  * {
    font-family: 'Noto Nastaliq Urdu', serif !important;
  }
  input, textarea, select, button {
    font-family: 'Noto Nastaliq Urdu', serif !important;
  }
`;
document.head.appendChild(style);
</script>