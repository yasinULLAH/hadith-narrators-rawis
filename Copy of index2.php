<?php
// IsnadExplorerEdu - Single File PHP Application by Yasin Ullah
// Author: Yasin Ullah (Pakistani)
// Features: User Auth, Admin Panel, Hadith/Isnad Management, Narrator Bios, Backup/Restore

// 0. SESSION & ERROR REPORTING
ini_set('display_errors', 1); // 0 for production
ini_set('display_startup_errors', 1); // 0 for production
error_reporting(E_ALL); // E_NONE for production
session_start();

// 1. CONFIGURATION CONSTANTS
define('DB_FILE', './isnad_explorer.sqlite');
define('CSV_FILE', './all_rawis.csv'); // Place all_rawis.csv in the same directory
define('ADMIN_USERNAME', 'admin'); // Default admin username
define('ADMIN_PASSWORD', 'password123'); // Default admin password - CHANGE THIS!
define('APP_NAME', 'اسناد ایکسپلورر ایجو');

// 2. LANGUAGE ARRAY (URDU)
$lang = [
    'app_title' => APP_NAME,
    'login' => 'لاگ ان',
    'logout' => 'لاگ آؤٹ',
    'username' => 'صارف نام',
    'password' => 'پاس ورڈ',
    'admin_panel' => 'ایڈمن پینل',
    'user_panel' => 'صارف پینل',
    'home' => 'صفحہ اول',
    'hadith_list' => 'احادیث کی فہرست',
    'add_hadith' => 'نئی حدیث شامل کریں',
    'edit_hadith' => 'حدیث میں ترمیم کریں',
    'manage_hadiths' => 'احادیث کا انتظام',
    'manage_narrators' => 'راویوں کا انتظام',
    'edit_narrator_bio' => 'راوی کا تعارف ترمیم کریں',
    'backup_db' => 'ڈیٹا بیس کا بیک اپ لیں',
    'restore_db' => 'ڈیٹا بیس بحال کریں',
    'select_hadith' => 'حدیث منتخب کریں',
    'isnad_chain' => 'سند کا سلسلہ',
    'narrator_bio' => 'راوی کا مختصر تعارف',
    'narrator_details' => 'راوی کی تفصیلات',
    'no_hadiths' => 'کوئی حدیث موجود نہیں۔',
    'no_narrators' => 'کوئی راوی موجود نہیں۔',
    'hadith_title_ar' => 'حدیث کا عنوان (عربی)',
    'hadith_title_ur' => 'حدیث کا عنوان (اردو/پشتو)',
    'hadith_matn' => 'متن حدیث',
    'hadith_source' => 'ماخذ حدیث',
    'narrators_in_isnad' => 'سند میں راوی (ترتیب سے ID)',
    'comma_separated_ids' => 'کوما سے الگ کردہ IDs',
    'save' => 'محفوظ کریں',
    'delete' => 'حذف کریں',
    'confirm_delete' => 'کیا آپ واقعی حذف کرنا چاہتے ہیں؟',
    'action_successful' => 'کارروائی کامیاب',
    'action_failed' => 'کارروائی ناکام',
    'invalid_request' => 'غلط درخواست',
    'login_required' => 'لاگ ان ضروری ہے',
    'admin_required' => 'ایڈمن رسائی درکار ہے',
    'file_upload_error' => 'فائل اپلوڈ میں خرابی',
    'invalid_file_type' => 'غلط فائل کی قسم',
    'restore_successful' => 'ڈیٹا بیس کامیابی سے بحال ہوگئی',
    'restore_warning' => 'خبردار: بحالی موجودہ ڈیٹا کو ختم کردے گی۔',
    'backup_download' => 'بیک اپ فائل ڈاؤن لوڈ کریں',
    'upload_backup_file' => 'بیک اپ فائل اپلوڈ کریں (.sqlite)',
    'disclaimer' => 'دستبرداری: یہ ایک آسان تعلیمی ٹول ہے۔ اسناد کی مکمل تحقیق کے لیے مستند علماء سے رجوع کریں۔',
    'narrator_name' => 'راوی کا نام',
    'narrator_grade' => 'درجہ',
    'birth_details' => 'پیدائش کی تفصیلات',
    'death_details' => 'وفات کی تفصیلات',
    'teachers' => 'اساتذہ',
    'students' => 'تلامذہ',
    'bio_notes' => 'تفصیلی حالات',
    'close' => 'بند کریں',
    'data_loaded_from_csv' => 'CSV سے ڈیٹا کامیابی سے لوڈ ہوگیا۔',
    'csv_not_found_or_empty' => 'CSV فائل نہیں ملی یا خالی ہے۔',
    'error_processing_csv' => 'CSV فائل پروسیسنگ میں خرابی۔',
    'tier' => 'طبقہ',
];

// 3. HELPER FUNCTIONS
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function get_message() {
    $message = '';
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
    }
    return $message;
}

function set_message($msg, $type = 'success') {
    $_SESSION['message'] = "<div class='message {$type}'>{$msg}</div>";
}

// 4. DATABASE CONNECTION FUNCTION
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_FILE);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection error. Please check logs. " . $e->getMessage());
        }
    }
    return $pdo;
}

// 5. DATABASE INITIALIZATION
function initializeDatabase() {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        is_admin INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS narrators (
        id INTEGER PRIMARY KEY, -- scholar_indx from CSV
        name TEXT NOT NULL,
        grade TEXT,
        birth_details TEXT,
        death_details TEXT,
        bio_notes TEXT,
        teachers_ids_str TEXT, -- Comma-separated IDs
        students_ids_str TEXT  -- Comma-separated IDs
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS hadiths (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title_ar TEXT,
        title_ur TEXT,
        matn TEXT,
        source TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS isnads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hadith_id INTEGER NOT NULL,
        narrator_id INTEGER NOT NULL,
        tier_level INTEGER NOT NULL,
        FOREIGN KEY (hadith_id) REFERENCES hadiths(id) ON DELETE CASCADE,
        FOREIGN KEY (narrator_id) REFERENCES narrators(id) ON DELETE CASCADE,
        UNIQUE(hadith_id, tier_level)
    )");

    // Create default admin user if no users exist
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, 1)");
        $stmt->execute([ADMIN_USERNAME, $hashed_password]);
        set_message("Default admin user created (admin/password123). Please change the password.", "info");
    }

    // Load narrators from CSV if table is empty
    $stmt = $db->query("SELECT COUNT(*) as count FROM narrators");
    if ($stmt->fetchColumn() == 0) {
        loadNarratorsFromCSV(CSV_FILE);
    }
}

// 6. AUTHENTICATION FUNCTIONS
function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    redirect('?');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    global $lang;
    if (!isLoggedIn()) {
        set_message($lang['login_required'], 'error');
        redirect('?action=login');
    }
}

function requireAdmin() {
    global $lang;
    requireLogin();
    if (!isAdmin()) {
        set_message($lang['admin_required'], 'error');
        redirect('?');
    }
}

// 7. NARRATOR FUNCTIONS
function loadNarratorsFromCSV($filePath) {
    global $lang;
    $db = getDB();
    if (!file_exists($filePath) || !is_readable($filePath)) {
        set_message($lang['csv_not_found_or_empty'], 'error');
        error_log("CSV file not found or not readable: " . $filePath);
        return false;
    }

    $file = fopen($filePath, 'r');
    if (!$file) {
        set_message($lang['error_processing_csv'], 'error');
        error_log("Could not open CSV file: " . $filePath);
        return false;
    }

    // Skip header row
    fgetcsv($file); 
    $count = 0;

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT OR IGNORE INTO narrators (id, name, grade, birth_details, death_details, bio_notes, teachers_ids_str, students_ids_str) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) < 25) { // Ensure enough columns
                error_log("Skipping malformed CSV row: " . implode(",", $row));
                continue;
            }
            // Map CSV columns to table fields
            $id = !empty($row[0]) ? (int)$row[0] : null;
            if ($id === null) {
                 error_log("Skipping CSV row due to missing ID: " . implode(",", $row));
                 continue;
            }

            $name = $row[1] ?? '';
            $grade = $row[2] ?? '';
            
            $birth_details = ($row[7] ?? '') . ' | ' . ($row[17] ?? '') . ' | ' . ($row[18] ?? '');
            $death_details = ($row[9] ?? '') . ' | ' . ($row[23] ?? '') . ' | ' . ($row[24] ?? '');
            
            $bio_parts = [
                isset($row[3]) && !empty($row[3]) ? "Parents: " . $row[3] : "",
                isset($row[4]) && !empty($row[4]) ? "Spouse(s): " . $row[4] : "",
                isset($row[5]) && !empty($row[5]) ? "Siblings: " . $row[5] : "",
                isset($row[6]) && !empty($row[6]) ? "Children: " . $row[6] : "",
                isset($row[12]) && !empty($row[12]) ? "Area of Interest: " . $row[12] : "",
                isset($row[13]) && !empty($row[13]) ? "Tags: " . $row[13] : "",
                isset($row[14]) && !empty($row[14]) && $row[14] !== 'NA' ? "Books: " . $row[14] : "",
            ];
            $bio_notes = implode("\n", array_filter($bio_parts));

            $teachers_ids_str = $row[16] ?? '';
            $students_ids_str = $row[15] ?? '';

            $stmt->execute([$id, $name, $grade, $birth_details, $death_details, $bio_notes, $teachers_ids_str, $students_ids_str]);
            $count++;
        }
        $db->commit();
        set_message($count . ' ' . $lang['data_loaded_from_csv'], 'success');
    } catch (Exception $e) {
        $db->rollBack();
        set_message($lang['error_processing_csv'] . ': ' . $e->getMessage(), 'error');
        error_log("Error processing CSV: " . $e->getMessage());
        return false;
    } finally {
        fclose($file);
    }
    return true;
}

function getAllNarrators() {
    $db = getDB();
    return $db->query("SELECT id, name FROM narrators ORDER BY name COLLATE NOCASE")->fetchAll();
}

function getNarratorById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM narrators WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateNarratorBio($id, $bio_notes) {
    global $lang;
    $db = getDB();
    $stmt = $db->prepare("UPDATE narrators SET bio_notes = ? WHERE id = ?");
    if ($stmt->execute([$bio_notes, $id])) {
        set_message($lang['action_successful'], 'success');
        return true;
    }
    set_message($lang['action_failed'], 'error');
    return false;
}

function getNarratorNameById($id) {
    $narrator = getNarratorById($id);
    return $narrator ? $narrator['name'] : "Unknown Narrator (ID: $id)";
}


// 8. HADITH FUNCTIONS
function addHadith($title_ar, $title_ur, $matn, $source) {
    global $lang;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO hadiths (title_ar, title_ur, matn, source) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title_ar, $title_ur, $matn, $source])) {
        set_message($lang['action_successful'], 'success');
        return $db->lastInsertId();
    }
    set_message($lang['action_failed'], 'error');
    return false;
}

function updateHadith($id, $title_ar, $title_ur, $matn, $source) {
    global $lang;
    $db = getDB();
    $stmt = $db->prepare("UPDATE hadiths SET title_ar = ?, title_ur = ?, matn = ?, source = ? WHERE id = ?");
    if ($stmt->execute([$title_ar, $title_ur, $matn, $source, $id])) {
        set_message($lang['action_successful'], 'success');
        return true;
    }
    set_message($lang['action_failed'], 'error');
    return false;
}

function deleteHadith($id) {
    global $lang;
    $db = getDB();
    // Associated isnads will be deleted by ON DELETE CASCADE
    $stmt = $db->prepare("DELETE FROM hadiths WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_message($lang['action_successful'], 'success');
        return true;
    }
    set_message($lang['action_failed'], 'error');
    return false;
}

function getHadithById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM hadiths WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllHadiths() {
    $db = getDB();
    return $db->query("SELECT id, title_ur, title_ar FROM hadiths ORDER BY id DESC")->fetchAll();
}

// 9. ISNAD FUNCTIONS
function getIsnadForHadith($hadith_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT i.narrator_id, i.tier_level, n.name as narrator_name 
                          FROM isnads i
                          JOIN narrators n ON i.narrator_id = n.id
                          WHERE i.hadith_id = ? 
                          ORDER BY i.tier_level ASC");
    $stmt->execute([$hadith_id]);
    return $stmt->fetchAll();
}

function updateIsnadForHadith($hadith_id, $narrator_ids_str) {
    global $lang;
    $db = getDB();
    $narrator_ids = array_map('intval', array_filter(explode(',', $narrator_ids_str)));

    $db->beginTransaction();
    try {
        // Delete old isnad links for this hadith
        $stmt = $db->prepare("DELETE FROM isnads WHERE hadith_id = ?");
        $stmt->execute([$hadith_id]);

        // Add new isnad links
        $stmt = $db->prepare("INSERT INTO isnads (hadith_id, narrator_id, tier_level) VALUES (?, ?, ?)");
        foreach ($narrator_ids as $index => $narrator_id) {
            if ($narrator_id > 0) { // Ensure narrator ID is valid
                 // Check if narrator exists
                $narratorExistsStmt = $db->prepare("SELECT COUNT(*) FROM narrators WHERE id = ?");
                $narratorExistsStmt->execute([$narrator_id]);
                if ($narratorExistsStmt->fetchColumn() == 0) {
                    throw new Exception("Narrator with ID {$narrator_id} does not exist.");
                }
                $stmt->execute([$hadith_id, $narrator_id, $index + 1]);
            }
        }
        $db->commit();
        set_message($lang['action_successful'], 'success');
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        set_message($lang['action_failed'] . ': ' . $e->getMessage(), 'error');
        error_log("Isnad update error: " . $e->getMessage());
        return false;
    }
}


// 10. BACKUP/RESTORE FUNCTIONS
function backupDatabase() {
    global $lang;
    requireAdmin();
    $backup_file_name = 'isnad_explorer_backup_' . date('Y-m-d_H-i-s') . '.sqlite';
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($backup_file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(DB_FILE));
    readfile(DB_FILE);
    exit;
}

function restoreDatabase($file_path) {
    global $lang;
    requireAdmin();
    
    // Basic validation
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    if ($file_extension !== 'sqlite' && $file_extension !== 'db') {
        set_message($lang['invalid_file_type'] . " (must be .sqlite or .db)", 'error');
        @unlink($file_path); // Delete uploaded temp file
        return false;
    }

    // Close current DB connection if any (though PDO static var might persist)
    // For safety, it's better to ensure no locks. Simplest is to replace file.
    
    if (copy($file_path, DB_FILE)) {
        set_message($lang['restore_successful'], 'success');
        @unlink($file_path); // Delete uploaded temp file
        // Re-initialize to ensure schema is okay, or trust the backup.
        // For now, trust the backup. A more robust solution might validate the restored DB.
        return true;
    } else {
        set_message($lang['action_failed'] . " Could not copy backup file.", 'error');
        error_log("Restore failed: Could not copy " . $file_path . " to " . DB_FILE);
        @unlink($file_path); // Delete uploaded temp file
        return false;
    }
}

// Initialize database (creates tables, loads CSV if needed)
initializeDatabase();

// 11. REQUEST HANDLING (ROUTING LOGIC)
$action = $_GET['action'] ?? 'home';
$page_content = ''; // To store HTML for main content area

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    if ($post_action === 'login') {
        if (login($_POST['username'], $_POST['password'])) {
            redirect('?action=admin');
        } else {
            set_message('Login failed.', 'error');
            redirect('?action=login');
        }
    } elseif ($post_action === 'add_hadith') {
        requireAdmin();
        $hadith_id = addHadith($_POST['title_ar'], $_POST['title_ur'], $_POST['matn'], $_POST['source']);
        if ($hadith_id && !empty($_POST['narrator_ids'])) {
            updateIsnadForHadith($hadith_id, $_POST['narrator_ids']);
        }
        redirect('?action=manage_hadiths');
    } elseif ($post_action === 'edit_hadith') {
        requireAdmin();
        $hadith_id = $_POST['hadith_id'];
        updateHadith($hadith_id, $_POST['title_ar'], $_POST['title_ur'], $_POST['matn'], $_POST['source']);
        if (!empty($_POST['narrator_ids'])) {
            updateIsnadForHadith($hadith_id, $_POST['narrator_ids']);
        } else { // Clear isnad if empty string submitted
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM isnads WHERE hadith_id = ?");
            $stmt->execute([$hadith_id]);
        }
        redirect('?action=manage_hadiths');
    } elseif ($post_action === 'delete_hadith') {
        requireAdmin();
        deleteHadith($_POST['hadith_id']);
        redirect('?action=manage_hadiths');
    } elseif ($post_action === 'edit_narrator_bio') {
        requireAdmin();
        updateNarratorBio($_POST['narrator_id'], $_POST['bio_notes']);
        redirect('?action=edit_narrator&id=' . $_POST['narrator_id']);
    } elseif ($post_action === 'restore_db') {
        requireAdmin();
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['backup_file']['tmp_name'];
            if (restoreDatabase($tmp_name)) {
                 // Success message set by function
            } else {
                // Error message set by function
            }
        } else {
            set_message($lang['file_upload_error'] . " Code: " . ($_FILES['backup_file']['error'] ?? 'Unknown'), 'error');
        }
        redirect('?action=admin');
    }
}

// Handle GET requests
if ($action === 'logout') {
    logout();
} elseif ($action === 'backup_db') {
    backupDatabase(); // This exits
} elseif ($action === 'get_narrator_details' && isset($_GET['narrator_id'])) {
    // AJAX endpoint
    header('Content-Type: application/json; charset=utf-8');
    $narrator = getNarratorById((int)$_GET['narrator_id']);
    if ($narrator) {
        // Fetch teacher and student names
        $teacher_ids = array_filter(explode(',', $narrator['teachers_ids_str']));
        $student_ids = array_filter(explode(',', $narrator['students_ids_str']));
        $narrator['teacher_names'] = array_map('getNarratorNameById', $teacher_ids);
        $narrator['student_names'] = array_map('getNarratorNameById', $student_ids);
        echo json_encode($narrator);
    } else {
        echo json_encode(['error' => 'Narrator not found']);
    }
    exit;
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ur">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($lang['app_title']); ?></title>
    <style>
        body {
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #1a1a2e; /* Dark blue-purple */
            color: #e0e0e0; /* Light grey text */
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #2a2a3e; /* Slightly lighter dark shade */
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #4f4f6a; /* Accent color */
            margin-bottom: 20px;
        }
        header h1 {
            color: #87CEFA; /* Light sky blue */
            margin: 0;
        }
        nav {
            margin-top: 10px;
        }
        nav a {
            color: #87CEFA;
            text-decoration: none;
            margin: 0 15px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav a:hover, nav a.active {
            background-color: #4f4f6a;
            color: #fff;
        }
        main {
            padding: 10px 0;
        }
        h2 { color: #98FB98; /* Pale green */ }
        h3 { color: #FFD700; /* Gold */ }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .message.success { background-color: #38761d; color: #d9ead3; border: 1px solid #2a5713;}
        .message.error { background-color: #a02c2c; color: #f4cccc; border: 1px solid #741b1b;}
        .message.info { background-color: #2c5ca0; color: #d0e0f0; border: 1px solid #1b3a74;}

        form { display: flex; flex-direction: column; gap: 15px; }
        form label { font-weight: bold; color: #ADD8E6; /* Light blue */ }
        form input[type="text"],
        form input[type="password"],
        form textarea,
        form select {
            padding: 10px;
            border: 1px solid #4f4f6a;
            border-radius: 4px;
            background-color: #1e1e2f;
            color: #e0e0e0;
            width: 100%;
            box-sizing: border-box;
        }
        form textarea { min-height: 100px; resize: vertical; }
        form input[type="submit"], form button {
            background-color: #87CEFA;
            color: #1a1a2e;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        form input[type="submit"]:hover, form button:hover {
            background-color: #6495ED; /* Cornflower blue */
        }
        .form-group { margin-bottom: 15px; }

        .hadith-list, .narrator-list { list-style: none; padding: 0; }
        .hadith-list li, .narrator-list li {
            background-color: #2f2f4f;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 5px;
            border-right: 5px solid #87CEFA;
        }
        .hadith-list li a, .narrator-list li a { color: #98FB98; text-decoration: none; }
        .hadith-list li a:hover, .narrator-list li a:hover { text-decoration: underline; }
        .hadith-actions a { margin-left: 10px; color: #FFD700; }

        .isnad-display { margin-top: 20px; padding: 15px; background-color: #252535; border-radius: 5px; }
        .isnad-display ol { padding-right: 20px; }
        .isnad-display li { margin-bottom: 8px; }
        .isnad-display .narrator-link { color: #FFB6C1; /* Light pink */ cursor: pointer; text-decoration: underline; }
        .isnad-display .narrator-link:hover { color: #FFA07A; /* Light salmon */ }

        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; 
            background-color: rgba(0,0,0,0.7);
        }
        .modal-content {
            background-color: #2a2a3e;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #4f4f6a;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            color: #e0e0e0;
            position: relative;
        }
        .modal-close {
            color: #aaa;
            position: absolute;
            left: 15px; /* RTL: left */
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-close:hover, .modal-close:focus { color: #fff; text-decoration: none; }
        .modal-content h3 { margin-top: 0; color: #87CEFA; }
        .modal-content p { margin-bottom: 10px; }
        .modal-content strong { color: #ADD8E6; }
        .modal-content pre {
            background-color: #1e1e2f;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #4f4f6a;
            font-size: 0.9em;
            color: #aaa;
        }
        @media (max-width: 768px) {
            .container { width: 95%; padding: 15px; }
            nav a { margin: 0 5px; font-size: 0.9em; }
            .modal-content { width: 90%; margin: 15% auto; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo h($lang['app_title']); ?></h1>
            <nav>
                <a href="?" class="<?php echo ($action === 'home' || $action === '') ? 'active' : ''; ?>"><?php echo h($lang['home']); ?></a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="?action=admin" class="<?php echo ($action === 'admin' || strpos($action, 'manage_') === 0 || strpos($action, 'edit_') === 0) ? 'active' : ''; ?>"><?php echo h($lang['admin_panel']); ?></a>
                    <?php endif; ?>
                    <a href="?action=logout"><?php echo h($lang['logout']); ?> (<?php echo h($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="?action=login" class="<?php echo ($action === 'login') ? 'active' : ''; ?>"><?php echo h($lang['login']); ?></a>
                <?php endif; ?>
            </nav>
        </header>

        <main>
            <?php echo get_message(); ?>

            <?php if ($action === 'login'): ?>
                <h2><?php echo h($lang['login']); ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="username"><?php echo h($lang['username']); ?>:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo h($lang['password']); ?>:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <input type="submit" value="<?php echo h($lang['login']); ?>">
                </form>

            <?php elseif ($action === 'admin'): requireAdmin(); ?>
                <h2><?php echo h($lang['admin_panel']); ?></h2>
                <ul>
                    <li><a href="?action=manage_hadiths"><?php echo h($lang['manage_hadiths']); ?></a></li>
                    <li><a href="?action=manage_narrators"><?php echo h($lang['manage_narrators']); ?></a></li>
                </ul>
                <h3><?php echo h($lang['backup_db']); ?></h3>
                <p><a href="?action=backup_db" class="button"><?php echo h($lang['backup_download']); ?></a></p>
                
                <h3><?php echo h($lang['restore_db']); ?></h3>
                <p style="color: #FFD700;"><?php echo h($lang['restore_warning']); ?></p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="restore_db">
                    <div class="form-group">
                        <label for="backup_file"><?php echo h($lang['upload_backup_file']); ?>:</label>
                        <input type="file" id="backup_file" name="backup_file" accept=".sqlite,.db" required>
                    </div>
                    <input type="submit" value="<?php echo h($lang['restore_db']); ?>">
                </form>

            <?php elseif ($action === 'manage_hadiths'): requireAdmin(); ?>
                <h2><?php echo h($lang['manage_hadiths']); ?></h2>
                <p><a href="?action=add_hadith_form" class="button"><?php echo h($lang['add_hadith']); ?></a></p>
                <?php $hadiths = getAllHadiths(); ?>
                <?php if (empty($hadiths)): ?>
                    <p><?php echo h($lang['no_hadiths']); ?></p>
                <?php else: ?>
                    <ul class="hadith-list">
                        <?php foreach ($hadiths as $hadith): ?>
                            <li>
                                <strong><?php echo h($hadith['title_ur'] ?: $hadith['title_ar']); ?></strong>
                                <div class="hadith-actions">
                                    <a href="?action=edit_hadith_form&id=<?php echo $hadith['id']; ?>"><?php echo h($lang['edit_hadith']); ?></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('<?php echo h($lang['confirm_delete']); ?>');">
                                        <input type="hidden" name="action" value="delete_hadith">
                                        <input type="hidden" name="hadith_id" value="<?php echo $hadith['id']; ?>">
                                        <button type="submit" class="link-button"><?php echo h($lang['delete']); ?></button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <style>.link-button { background:none;border:none;color:#FFD700;cursor:pointer;padding:0;font:inherit;text-decoration:underline; }</style>
                <?php endif; ?>

            <?php elseif ($action === 'add_hadith_form' || ($action === 'edit_hadith_form' && isset($_GET['id']))): requireAdmin(); ?>
                <?php
                $is_edit = ($action === 'edit_hadith_form');
                $hadith_data = null;
                $current_isnad_ids = '';
                if ($is_edit) {
                    $hadith_data = getHadithById((int)$_GET['id']);
                    if (!$hadith_data) { set_message($lang['invalid_request'], 'error'); redirect('?action=manage_hadiths'); }
                    $isnad_links = getIsnadForHadith($hadith_data['id']);
                    $current_isnad_ids = implode(',', array_column($isnad_links, 'narrator_id'));
                }
                $all_narrators = getAllNarrators();
                ?>
                <h2><?php echo h($is_edit ? $lang['edit_hadith'] : $lang['add_hadith']); ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_hadith' : 'add_hadith'; ?>">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="hadith_id" value="<?php echo $hadith_data['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="title_ar"><?php echo h($lang['hadith_title_ar']); ?>:</label>
                        <input type="text" id="title_ar" name="title_ar" value="<?php echo h($hadith_data['title_ar'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="title_ur"><?php echo h($lang['hadith_title_ur']); ?>:</label>
                        <input type="text" id="title_ur" name="title_ur" value="<?php echo h($hadith_data['title_ur'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="matn"><?php echo h($lang['hadith_matn']); ?>:</label>
                        <textarea id="matn" name="matn"><?php echo h($hadith_data['matn'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="source"><?php echo h($lang['hadith_source']); ?>:</label>
                        <input type="text" id="source" name="source" value="<?php echo h($hadith_data['source'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="narrator_ids"><?php echo h($lang['narrators_in_isnad']); ?> (<?php echo h($lang['comma_separated_ids']); ?>):</label>
                        <input type="text" id="narrator_ids" name="narrator_ids" value="<?php echo h($current_isnad_ids); ?>" placeholder="e.g., 1,5,12">
                        <small>Select from available narrators below or enter IDs directly. Order matters.</small>
                        <select multiple size="5" onchange="document.getElementById('narrator_ids').value = Array.from(this.selectedOptions).map(option => option.value).join(',')">
                            <?php foreach ($all_narrators as $narrator): ?>
                            <option value="<?php echo $narrator['id']; ?>"><?php echo h($narrator['id'] . ': ' . $narrator['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="submit" value="<?php echo h($lang['save']); ?>">
                </form>

            <?php elseif ($action === 'manage_narrators'): requireAdmin(); ?>
                <h2><?php echo h($lang['manage_narrators']); ?></h2>
                <?php $narrators = getAllNarrators(); ?>
                <?php if (empty($narrators)): ?>
                    <p><?php echo h($lang['no_narrators']); ?></p>
                <?php else: ?>
                    <ul class="narrator-list">
                        <?php foreach ($narrators as $narrator): ?>
                            <li>
                                <a href="?action=edit_narrator&id=<?php echo $narrator['id']; ?>">
                                    <?php echo h($narrator['name']); ?> (ID: <?php echo $narrator['id']; ?>)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            <?php elseif ($action === 'edit_narrator' && isset($_GET['id'])): requireAdmin(); ?>
                <?php
                $narrator = getNarratorById((int)$_GET['id']);
                if (!$narrator) { set_message($lang['invalid_request'], 'error'); redirect('?action=manage_narrators'); }
                ?>
                <h2><?php echo h($lang['edit_narrator_bio']); ?>: <?php echo h($narrator['name']); ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="edit_narrator_bio">
                    <input type="hidden" name="narrator_id" value="<?php echo $narrator['id']; ?>">
                    <div class="form-group">
                        <label><?php echo h($lang['narrator_name']); ?>:</label>
                        <p><?php echo h($narrator['name']); ?></p>
                    </div>
                    <div class="form-group">
                        <label><?php echo h($lang['narrator_grade']); ?>:</label>
                        <p><?php echo h($narrator['grade']); ?></p>
                    </div>
                    <div class="form-group">
                        <label><?php echo h($lang['birth_details']); ?>:</label>
                        <p><?php echo h($narrator['birth_details']); ?></p>
                    </div>
                    <div class="form-group">
                        <label><?php echo h($lang['death_details']); ?>:</label>
                        <p><?php echo h($narrator['death_details']); ?></p>
                    </div>
                    <div class="form-group">
                        <label for="bio_notes"><?php echo h($lang['bio_notes']); ?>:</label>
                        <textarea id="bio_notes" name="bio_notes" rows="10"><?php echo h($narrator['bio_notes']); ?></textarea>
                    </div>
                    <input type="submit" value="<?php echo h($lang['save']); ?>">
                </form>

            <?php elseif ($action === 'view_hadith' && isset($_GET['id'])): ?>
                <?php
                $hadith = getHadithById((int)$_GET['id']);
                if (!$hadith) {
                    set_message($lang['invalid_request'], 'error');
                    redirect('?');
                }
                $isnad_chain = getIsnadForHadith($hadith['id']);
                ?>
                <h2><?php echo h($hadith['title_ur'] ?: $hadith['title_ar']); ?></h2>
                <?php if ($hadith['title_ar'] && $hadith['title_ur']): ?>
                    <p><em><?php echo h($hadith['title_ar']); ?></em></p>
                <?php endif; ?>
                <?php if ($hadith['source']): ?>
                    <p><strong><?php echo h($lang['hadith_source']); ?>:</strong> <?php echo h($hadith['source']); ?></p>
                <?php endif; ?>
                <?php if ($hadith['matn']): ?>
                    <h3><?php echo h($lang['hadith_matn']); ?></h3>
                    <p style="white-space: pre-wrap;"><?php echo h($hadith['matn']); ?></p>
                <?php endif; ?>

                <h3><?php echo h($lang['isnad_chain']); ?></h3>
                <?php if (empty($isnad_chain)): ?>
                    <p>No Isnad information available for this Hadith.</p>
                <?php else: ?>
                    <div class="isnad-display">
                        <ol>
                            <?php foreach ($isnad_chain as $link): ?>
                                <li>
                                    <span class="narrator-link" data-narrator-id="<?php echo $link['narrator_id']; ?>">
                                        <?php echo h($link['narrator_name']); ?>
                                    </span>
                                    (<?php echo h($lang['tier']); ?> <?php echo $link['tier_level']; ?>)
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>

            <?php else: // Home page / Default action ?>
                <h2><?php echo h($lang['hadith_list']); ?></h2>
                <?php $hadiths = getAllHadiths(); ?>
                <?php if (empty($hadiths)): ?>
                    <p><?php echo h($lang['no_hadiths']); ?></p>
                <?php else: ?>
                    <ul class="hadith-list">
                        <?php foreach ($hadiths as $hadith): ?>
                            <li>
                                <a href="?action=view_hadith&id=<?php echo $hadith['id']; ?>">
                                    <?php echo h($hadith['title_ur'] ?: $hadith['title_ar']); ?>
                                </a>
                                <?php if ($hadith['title_ar'] && $hadith['title_ur']): ?>
                                    <br><small><em><?php echo h($hadith['title_ar']); ?></em></small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <footer>
            <p><?php echo h($lang['disclaimer']); ?></p>
            <p>© <?php echo date('Y'); ?> Yasin Ullah. Pakistani.</p>
        </footer>
    </div>

    <div id="narratorModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">×</span>
            <h3 id="modalNarratorName"></h3>
            <p><strong><?php echo h($lang['narrator_grade']); ?>:</strong> <span id="modalNarratorGrade"></span></p>
            <p><strong><?php echo h($lang['birth_details']); ?>:</strong> <span id="modalNarratorBirth"></span></p>
            <p><strong><?php echo h($lang['death_details']); ?>:</strong> <span id="modalNarratorDeath"></span></p>
            <p><strong><?php echo h($lang['teachers']); ?>:</strong> <span id="modalNarratorTeachers"></span></p>
            <p><strong><?php echo h($lang['students']); ?>:</strong> <span id="modalNarratorStudents"></span></p>
            <p><strong><?php echo h($lang['bio_notes']); ?>:</strong></p>
            <pre id="modalNarratorBio"></pre>
        </div>
    </div>

    <script>
        const modal = document.getElementById('narratorModal');
        const narratorLinks = document.querySelectorAll('.narrator-link');

        narratorLinks.forEach(link => {
            link.addEventListener('click', function() {
                const narratorId = this.dataset.narratorId;
                fetch(`?action=get_narrator_details&narrator_id=${narratorId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        document.getElementById('modalNarratorName').textContent = data.name || 'N/A';
                        document.getElementById('modalNarratorGrade').textContent = data.grade || 'N/A';
                        document.getElementById('modalNarratorBirth').textContent = data.birth_details || 'N/A';
                        document.getElementById('modalNarratorDeath').textContent = data.death_details || 'N/A';
                        document.getElementById('modalNarratorBio').textContent = data.bio_notes || 'N/A';
                        
                        document.getElementById('modalNarratorTeachers').textContent = data.teacher_names && data.teacher_names.length > 0 ? data.teacher_names.join(', ') : 'N/A';
                        document.getElementById('modalNarratorStudents').textContent = data.student_names && data.student_names.length > 0 ? data.student_names.join(', ') : 'N/A';

                        modal.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching narrator details:', error);
                        alert('Error fetching details.');
                    });
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Confirm delete
        function confirmDelete() {
            return confirm("<?php echo h($lang['confirm_delete']); ?>");
        }
    </script>
</body>
</html>