<?php
// Show all errors except warnings, notices, and deprecated
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
<?php
/**
 * IsnadExplorerEdu - An Educational Tool for Exploring Hadith Isnad
 * Author: Yasin Ullah (Pakistan)
 *
 * This single-file PHP application provides a simplified view of Hadith Isnads
 * for educational purposes. It uses SQLite for data storage and PDO for database
 * interaction. The UI is designed to be futuristic, clear, visual, and responsive,
 * with support for Urdu/Pashto and Arabic names.
 *
 * Features:
 * - Secure user authentication (basic).
 * - Admin-managed database of selected Hadith and their simplified Isnad data.
 * - User selects a Hadith to view its Isnad.
 * - Isnad displayed in a clear list or basic tree structure.
 * - Brief bio notes for prominent narrators (admin-managed).
 * - Disclaimer about the simplified nature of the tool.
 * - Robust validation.
 * - Backup and Restore functionality for the database.
 * - Auto-loading of initial narrator data from a CSV file on first run.
 *
 * Disclaimer: This tool provides a simplified representation of Hadith Isnads
 * for educational purposes only. It is not a substitute for in-depth study
 * of Hadith sciences and authentic sources. The information provided should
 * be verified with reliable scholarly resources.
 */

// --- Configuration ---
define('DB_FILE', __DIR__ . '/isnad_explorer.sqlite');
define('CSV_FILE', __DIR__ . '/all_rawis.csv');
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'adminpass'); // **CHANGE THIS IN PRODUCTION**
define('APP_NAME', 'اسناد ایکسپلورر'); // App name in Urdu/Pashto
define('LANG', 'ur'); // Default language: 'ur' for Urdu, 'ps' for Pashto

// --- Database Initialization ---
function init_db() {
    if (!file_exists(DB_FILE)) {
        try {
            $db = new PDO('sqlite:' . DB_FILE);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create tables
            $db->exec("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user'
            )");

            $db->exec("CREATE TABLE narrators (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                scholar_indx INTEGER UNIQUE,
                name TEXT NOT NULL,
                grade TEXT,
                parents TEXT,
                spouse TEXT,
                siblings TEXT,
                children TEXT,
                birth_date_place TEXT,
                places_of_stay TEXT,
                death_date_place TEXT,
                teachers TEXT,
                students TEXT,
                area_of_interest TEXT,
                tags TEXT,
                books TEXT,
                students_inds TEXT,
                teachers_inds TEXT,
                birth_place TEXT,
                birth_date TEXT,
                birth_date_hijri INTEGER,
                birth_date_gregorian INTEGER,
                death_date_hijri INTEGER,
                death_date_gregorian INTEGER,
                death_place TEXT,
                death_reason TEXT,
                bio TEXT
            )");

            $db->exec("CREATE TABLE hadith (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                text TEXT NOT NULL,
                isnad TEXT NOT NULL -- Comma-separated list of narrator IDs
            )");

            // Insert admin user
            $hashed_password = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([ADMIN_USERNAME, $hashed_password, 'admin']);

            // Load initial narrator data from CSV
            load_narrators_from_csv($db);

        } catch (PDOException $e) {
            die("Database initialization failed: " . $e->getMessage());
        }
    }
}

// --- CSV Data Loading ---
function load_narrators_from_csv($db) {
    if (!file_exists(CSV_FILE)) {
        return; // No CSV file to load
    }

    $handle = fopen(CSV_FILE, "r");
    if ($handle === FALSE) {
        error_log("Failed to open CSV file: " . CSV_FILE);
        return;
    }

    $header = fgetcsv($handle); // Read header row

    $stmt = $db->prepare("INSERT INTO narrators (scholar_indx, name, grade, parents, spouse, siblings, children, birth_date_place, places_of_stay, death_date_place, teachers, students, area_of_interest, tags, books, students_inds, teachers_inds, birth_place, birth_date, birth_date_hijri, birth_date_gregorian, death_date_hijri, death_date_gregorian, death_place, death_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    while (($data = fgetcsv($handle)) !== FALSE) {
        // Ensure correct number of columns
        if (count($data) == count($header)) {
            // Map CSV data to database columns
            $rowData = array_combine($header, $data);

            // Prepare data for insertion, handling potential empty values
            $insertData = [
                isset($rowData['scholar_indx']) ? (int)$rowData['scholar_indx'] : null,
                isset($rowData['name']) ? $rowData['name'] : '',
                isset($rowData['grade']) ? $rowData['grade'] : '',
                isset($rowData['parents']) ? $rowData['parents'] : '',
                isset($rowData['spouse']) ? $rowData['spouse'] : '',
                isset($rowData['siblings']) ? $rowData['siblings'] : '',
                isset($rowData['children']) ? $rowData['children'] : '',
                isset($rowData['birth_date_place']) ? $rowData['birth_date_place'] : '',
                isset($rowData['places_of_stay']) ? $rowData['places_of_stay'] : '',
                isset($rowData['death_date_place']) ? $rowData['death_date_place'] : '',
                isset($rowData['teachers']) ? $rowData['teachers'] : '',
                isset($rowData['students']) ? $rowData['students'] : '',
                isset($rowData['area_of_interest']) ? $rowData['area_of_interest'] : '',
                isset($rowData['tags']) ? $rowData['tags'] : '',
                isset($rowData['books']) ? $rowData['books'] : '',
                isset($rowData['students_inds']) ? $rowData['students_inds'] : '',
                isset($rowData['teachers_inds']) ? $rowData['teachers_inds'] : '',
                isset($rowData['birth_place']) ? $rowData['birth_place'] : '',
                isset($rowData['birth_date']) ? $rowData['birth_date'] : '',
                isset($rowData['birth_date_hijri']) && is_numeric($rowData['birth_date_hijri']) ? (int)$rowData['birth_date_hijri'] : null,
                isset($rowData['birth_date_gregorian']) && is_numeric($rowData['birth_date_gregorian']) ? (int)$rowData['birth_date_gregorian'] : null,
                isset($rowData['death_date_hijri']) && is_numeric($rowData['death_date_hijri']) ? (int)$rowData['death_date_hijri'] : null,
                isset($rowData['death_date_gregorian']) && is_numeric($rowData['death_date_gregorian']) ? (int)$rowData['death_date_gregorian'] : null,
                isset($rowData['death_place']) ? $rowData['death_place'] : '',
                isset($rowData['death_reason']) ? $rowData['death_reason'] : '',
            ];

            try {
                $stmt->execute($insertData);
            } catch (PDOException $e) {
                // Log the error but continue processing other rows
                error_log("Error inserting narrator data: " . $e->getMessage() . " - Data: " . implode(',', $data));
            }
        } else {
            error_log("Skipping row due to incorrect column count: " . implode(',', $data));
        }
    }

    fclose($handle);
}


// --- Authentication ---
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function login($username, $password) {
    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Login failed: " . $e->getMessage());
        return false;
    }
}

function logout() {
    session_unset();
    session_destroy();
}

// --- Database Operations ---
function get_db() {
    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function get_hadith_list() {
    $db = get_db();
    $stmt = $db->query("SELECT id, text FROM hadith");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_hadith_details($hadith_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM hadith WHERE id = ?");
    $stmt->execute([$hadith_id]);
    $hadith = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hadith) {
        $narrator_ids = explode(',', $hadith['isnad']);
        $narrators = [];
        if (!empty($narrator_ids)) {
            $placeholders = implode(',', array_fill(0, count($narrator_ids), '?'));
            $stmt = $db->prepare("SELECT * FROM narrators WHERE id IN ($placeholders)");
            $stmt->execute($narrator_ids);
            $narrators_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Order narrators according to the isnad sequence
            $ordered_narrators = [];
            foreach ($narrator_ids as $id) {
                foreach ($narrators_data as $narrator) {
                    if ($narrator['id'] == $id) {
                        $ordered_narrators[] = $narrator;
                        break;
                    }
                }
            }
            $hadith['isnad_narrators'] = $ordered_narrators;
        } else {
             $hadith['isnad_narrators'] = [];
        }
    }
    return $hadith;
}


function get_narrator_bio($narrator_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM narrators WHERE id = ?");
    $stmt->execute([$narrator_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function add_hadith($text, $isnad) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO hadith (text, isnad) VALUES (?, ?)");
    return $stmt->execute([$text, $isnad]);
}

function update_hadith($id, $text, $isnad) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE hadith SET text = ?, isnad = ? WHERE id = ?");
    return $stmt->execute([$text, $isnad, $id]);
}

function delete_hadith($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM hadith WHERE id = ?");
    return $stmt->execute([$id]);
}

function get_narrators() {
    $db = get_db();
    $stmt = $db->query("SELECT id, name FROM narrators ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_narrator($data) {
    $db = get_db();
    $fields = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO narrators ($fields) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    return $stmt->execute(array_values($data));
}

function update_narrator($id, $data) {
    $db = get_db();
    $set_clauses = [];
    foreach ($data as $key => $value) {
        $set_clauses[] = "$key = ?";
    }
    $sql = "UPDATE narrators SET " . implode(', ', $set_clauses) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $values = array_values($data);
    $values[] = $id;
    return $stmt->execute($values);
}

function delete_narrator($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM narrators WHERE id = ?");
    return $stmt->execute([$id]);
}

// --- Backup and Restore ---
function backup_database() {
    $backup_file = 'isnad_explorer_backup_' . date('YmdHis') . '.sqlite';
    if (copy(DB_FILE, $backup_file)) {
        return $backup_file;
    } else {
        return false;
    }
}

function restore_database($backup_file) {
    if (file_exists($backup_file)) {
        if (copy($backup_file, DB_FILE)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// --- Language Strings (Urdu/Pashto - Simplified) ---
function lang($key) {
    $strings = [
        'ur' => [
            'app_name' => 'اسناد ایکسپلورر',
            'login_title' => 'لاگ ان کریں',
            'username' => 'یوزر نیم',
            'password' => 'پاس ورڈ',
            'login_button' => 'لاگ ان',
            'login_failed' => 'لاگ ان ناکام۔ یوزر نیم یا پاس ورڈ غلط ہے۔',
            'logout' => 'لاگ آؤٹ',
            'welcome_admin' => 'خوش آمدید، ایڈمن!',
            'welcome_user' => 'خوش آمدید، صارف!',
            'hadith_list' => 'احادیث کی فہرست',
            'select_hadith' => 'حدیث منتخب کریں',
            'view_isnad' => 'اسناد دیکھیں',
            'hadith_text' => 'حدیث کا متن',
            'isnad_chain' => 'اسناد کی کڑی',
            'narrator_bio' => 'راوی کا تعارف',
            'no_bio_available' => 'اس راوی کے لیے کوئی تعارف دستیاب نہیں۔',
            'disclaimer_title' => 'اہم دستبرداری',
            'disclaimer_text' => 'یہ ٹول صرف تعلیمی مقاصد کے لیے احادیث کی اسناد کی ایک سادہ نمائندگی فراہم کرتا ہے۔ یہ احادیث کے علوم اور مستند ذرائع کے گہرائی سے مطالعہ کا متبادل نہیں ہے۔ فراہم کردہ معلومات کی تصدیق قابل اعتماد علمی وسائل سے کی جانی چاہیے۔',
            'admin_panel' => 'ایڈمن پینل',
            'manage_hadith' => 'احادیث کا انتظام کریں',
            'manage_narrators' => 'راویوں کا انتظام کریں',
            'add_hadith' => 'نئی حدیث شامل کریں',
            'edit_hadith' => 'حدیث میں ترمیم کریں',
            'delete_hadith' => 'حدیث حذف کریں',
            'hadith_text_label' => 'حدیث کا متن:',
            'isnad_label' => 'اسناد (راوی IDs کوما سے الگ):',
            'save_hadith' => 'حدیث محفوظ کریں',
            'cancel' => 'منسوخ کریں',
            'add_narrator' => 'نیا راوی شامل کریں',
            'edit_narrator' => 'راوی میں ترمیم کریں',
            'delete_narrator' => 'راوی حذف کریں',
            'narrator_name' => 'راوی کا نام:',
            'narrator_bio_label' => 'راوی کا تعارف:',
            'save_narrator' => 'راوی محفوظ کریں',
            'confirm_delete_hadith' => 'کیا آپ واقعی اس حدیث کو حذف کرنا چاہتے ہیں؟',
            'confirm_delete_narrator' => 'کیا آپ واقعی اس راوی کو حذف کرنا چاہتے ہیں؟',
            'backup_db' => 'ڈیٹا بیس کا بیک اپ',
            'restore_db' => 'ڈیٹا بیس بحال کریں',
            'select_backup_file' => 'بیک اپ فائل منتخب کریں:',
            'upload_and_restore' => 'اپ لوڈ اور بحال کریں',
            'backup_success' => 'بیک اپ کامیابی سے بن گیا۔ فائل: %s',
            'backup_failed' => 'بیک اپ بنانے میں ناکام رہا۔',
            'restore_success' => 'ڈیٹا بیس کامیابی سے بحال ہو گیا۔',
            'restore_failed' => 'ڈیٹا بیس بحال کرنے میں ناکام رہا۔',
            'invalid_file_type' => 'غلط فائل کی قسم۔ صرف SQLite فائلیں مجاز ہیں۔',
            'upload_failed' => 'فائل اپ لوڈ کرنے میں ناکام رہا۔',
            'file_not_found' => 'بیک اپ فائل نہیں ملی۔',
            'scholar_indx' => 'اسکالر انڈیکس',
            'grade' => 'گریڈ',
            'parents' => 'والدین',
            'spouse' => 'شریک حیات',
            'siblings' => 'بہن بھائی',
            'children' => 'اولاد',
            'birth_date_place' => 'پیدائش کی تاریخ/مقام',
            'places_of_stay' => 'قیام کے مقامات',
            'death_date_place' => 'وفات کی تاریخ/مقام',
            'teachers' => 'اساتذہ',
            'students' => 'طلباء',
            'area_of_interest' => 'دلچسپی کا علاقہ',
            'tags' => 'ٹیگز',
            'books' => 'کتابیں',
            'students_inds' => 'طلباء انڈیکس',
            'teachers_inds' => 'اساتذہ انڈیکس',
            'birth_place' => 'مقام پیدائش',
            'birth_date' => 'تاریخ پیدائش',
            'birth_date_hijri' => 'تاریخ پیدائش (ہجری)',
            'birth_date_gregorian' => 'تاریخ پیدائش (عیسوی)',
            'death_date_hijri' => 'تاریخ وفات (ہجری)',
            'death_date_gregorian' => 'تاریخ وفات (عیسوی)',
            'death_place' => 'مقام وفات',
            'death_reason' => 'وفات کی وجہ',
        ],
        'ps' => [
            'app_name' => 'اسناد سپړونکی',
            'login_title' => 'ننوتل',
            'username' => 'کارن نوم',
            'password' => 'پټ نوم',
            'login_button' => 'ننوتل',
            'login_failed' => 'ننوتل ناکام شول. کارن نوم یا پټ نوم غلط دی.',
            'logout' => 'وتل',
            'welcome_admin' => 'ښه راغلاست، مدیر!',
            'welcome_user' => 'ښه راغلاست، کارن!',
            'hadith_list' => 'احادیث لیست',
            'select_hadith' => 'حدیث وټاکئ',
            'view_isnad' => 'اسناد وګورئ',
            'hadith_text' => 'حدیث متن',
            'isnad_chain' => 'اسناد لړۍ',
            'narrator_bio' => 'راوي پېژندنه',
            'no_bio_available' => 'د دې راوي لپاره پېژندنه نشته.',
            'disclaimer_title' => 'مهم یادونه',
            'disclaimer_text' => 'دا وسیله یوازې د تعلیمي موخو لپاره د احادیثو اسناد یوه ساده نمایش وړاندې کوي. دا د احادیثو علومو او مستند سرچینو ژورې مطالعې بدیل نه دی. چمتو شوي معلومات باید د باوري علمي سرچینو سره تایید شي.',
            'admin_panel' => 'مدیر پینل',
            'manage_hadith' => 'احادیث اداره کړئ',
            'manage_narrators' => 'راويان اداره کړئ',
            'add_hadith' => 'نوی حدیث اضافه کړئ',
            'edit_hadith' => 'حدیث سمول',
            'delete_hadith' => 'حدیث حذف کړئ',
            'hadith_text_label' => 'حدیث متن:',
            'isnad_label' => 'اسناد (راوي IDs د کوما په واسطه جلا شوي):',
            'save_hadith' => 'حدیث خوندي کړئ',
            'cancel' => 'لغوه کول',
            'add_narrator' => 'نوی راوي اضافه کړئ',
            'edit_narrator' => 'راوي سمول',
            'delete_narrator' => 'راوي حذف کړئ',
            'narrator_name' => 'راوي نوم:',
            'narrator_bio_label' => 'راوي پېژندنه:',
            'save_narrator' => 'راوي خوندي کړئ',
            'confirm_delete_hadith' => 'ایا تاسو واقعیا غواړئ دا حدیث حذف کړئ؟',
            'confirm_delete_narrator' => 'ایا تاسو واقعیا غواړئ دا راوي حذف کړئ؟',
            'backup_db' => 'ډیټابیس بیک اپ',
            'restore_db' => 'ډیټابیس بیرته راګرځول',
            'select_backup_file' => 'بیک اپ فایل وټاکئ:',
            'upload_and_restore' => 'اپلوډ او بیرته راګرځول',
            'backup_success' => 'بیک اپ په بریالیتوب سره جوړ شو. فایل: %s',
            'backup_failed' => 'بیک اپ جوړولو کې ناکام شو.',
            'restore_success' => 'ډیټابیس په بریالیتوب سره بیرته راګرځول شو.',
            'restore_failed' => 'ډیټابیس بیرته راګرځولو کې ناکام شو.',
            'invalid_file_type' => 'غلط فایل ډول. یوازې SQLite فایلونه مجاز دي.',
            'upload_failed' => 'فایل اپلوډ کولو کې ناکام شو.',
            'file_not_found' => 'بیک اپ فایل ونه موندل شو.',
            'scholar_indx' => 'اسکالر انډیکس',
            'grade' => 'درجه',
            'parents' => 'والدین',
            'spouse' => 'میرمن/خاوند',
            'siblings' => 'خویندې/وروڼه',
            'children' => 'اولاد',
            'birth_date_place' => 'د زیږون نیټه/ځای',
            'places_of_stay' => 'د اوسیدو ځایونه',
            'death_date_place' => 'د وفات نیټه/ځای',
            'teachers' => 'استادان',
            'students' => 'زده کوونکي',
            'area_of_interest' => 'د علاقې ساحه',
            'tags' => 'ټاګونه',
            'books' => 'کتابونه',
            'students_inds' => 'زده کوونکي انډیکس',
            'teachers_inds' => 'استادان انډیکس',
            'birth_place' => 'د زیږون ځای',
            'birth_date' => 'د زیږون نیټه',
            'birth_date_hijri' => 'د زیږون نیټه (هجري)',
            'birth_date_gregorian' => 'د زیږون نیټه (عیسوي)',
            'death_date_hijri' => 'د وفات نیټه (هجري)',
            'death_date_gregorian' => 'د وفات نیټه (عیسوي)',
            'death_place' => 'د وفات ځای',
            'death_reason' => 'د وفات لامل',
        ],
    ];
    return $strings[LANG][$key] ?? $key;
}

// --- Handle Actions ---
init_db();

$action = $_GET['action'] ?? 'home';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'login':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                if (login($username, $password)) {
                    header('Location: ?action=home');
                    exit;
                } else {
                    $error = lang('login_failed');
                }
                break;

            case 'logout':
                logout();
                header('Location: ?action=home');
                exit;

            case 'add_hadith':
                if (is_admin()) {
                    $text = $_POST['text'] ?? '';
                    $isnad = $_POST['isnad'] ?? '';
                    if (!empty($text) && !empty($isnad)) {
                        if (add_hadith($text, $isnad)) {
                            $message = 'حدیث کامیابی سے شامل ہو گئی۔';
                            header('Location: ?action=admin_hadith');
                            exit;
                        } else {
                            $error = 'حدیث شامل کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'حدیث کا متن اور اسناد درکار ہیں۔';
                    }
                }
                break;

            case 'update_hadith':
                if (is_admin()) {
                    $id = $_POST['id'] ?? 0;
                    $text = $_POST['text'] ?? '';
                    $isnad = $_POST['isnad'] ?? '';
                    if ($id > 0 && !empty($text) && !empty($isnad)) {
                        if (update_hadith($id, $text, $isnad)) {
                            $message = 'حدیث کامیابی سے اپ ڈیٹ ہو گئی۔';
                            header('Location: ?action=admin_hadith');
                            exit;
                        } else {
                            $error = 'حدیث اپ ڈیٹ کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'غلط حدیث ID یا درکار فیلڈز خالی ہیں۔';
                    }
                }
                break;

            case 'delete_hadith':
                if (is_admin()) {
                    $id = $_POST['id'] ?? 0;
                    if ($id > 0) {
                        if (delete_hadith($id)) {
                            $message = 'حدیث کامیابی سے حذف ہو گئی۔';
                            header('Location: ?action=admin_hadith');
                            exit;
                        } else {
                            $error = 'حدیث حذف کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'غلط حدیث ID۔';
                    }
                }
                break;

            case 'add_narrator':
                if (is_admin()) {
                    $data = $_POST;
                    unset($data['action']); // Remove action field
                    if (!empty($data['name'])) {
                         if (add_narrator($data)) {
                            $message = 'راوی کامیابی سے شامل ہو گیا۔';
                            header('Location: ?action=admin_narrators');
                            exit;
                        } else {
                            $error = 'راوی شامل کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'راوی کا نام درکار ہے۔';
                    }
                }
                break;

            case 'update_narrator':
                if (is_admin()) {
                    $id = $_POST['id'] ?? 0;
                    $data = $_POST;
                    unset($data['action']);
                    unset($data['id']);
                    if ($id > 0 && !empty($data['name'])) {
                        if (update_narrator($id, $data)) {
                            $message = 'راوی کامیابی سے اپ ڈیٹ ہو گیا۔';
                            header('Location: ?action=admin_narrators');
                            exit;
                        } else {
                            $error = 'راوی اپ ڈیٹ کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'غلط راوی ID یا درکار فیلڈز خالی ہیں۔';
                    }
                }
                break;

            case 'delete_narrator':
                if (is_admin()) {
                    $id = $_POST['id'] ?? 0;
                    if ($id > 0) {
                        if (delete_narrator($id)) {
                            $message = 'راوی کامیابی سے حذف ہو گیا۔';
                            header('Location: ?action=admin_narrators');
                            exit;
                        } else {
                            $error = 'راوی حذف کرنے میں ناکام رہا۔';
                        }
                    } else {
                        $error = 'غلط راوی ID۔';
                    }
                }
                break;

            case 'restore_db':
                if (is_admin() && isset($_FILES['backup_file'])) {
                    $file = $_FILES['backup_file'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $file_info = pathinfo($file['name']);
                        if (strtolower($file_info['extension']) === 'sqlite') {
                            $upload_path = sys_get_temp_dir() . '/' . basename($file['name']);
                            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                // Close existing database connection before restoring
                                $db = null;
                                if (restore_database($upload_path)) {
                                    $message = lang('restore_success');
                                } else {
                                    $error = lang('restore_failed');
                                }
                                unlink($upload_path); // Clean up temporary file
                            } else {
                                $error = lang('upload_failed');
                            }
                        } else {
                            $error = lang('invalid_file_type');
                        }
                    } else {
                        $error = lang('upload_failed') . ' Error code: ' . $file['error'];
                    }
                } else if (is_admin()) {
                     $error = lang('select_backup_file');
                }
                break;
        }
    }
}

// --- HTML Structure ---
?>
<!DOCTYPE html>
<html lang="<?php echo LANG; ?>" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('app_name'); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 0;
            direction: rtl; /* Right-to-left for Urdu/Pashto/Arabic */
            text-align: right;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            margin-bottom: 20px;
        }
        header h1 {
            margin: 0;
            font-size: 2em;
        }
        nav {
            margin-bottom: 20px;
            text-align: center;
        }
        nav a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding in width */
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .hadith-item {
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .hadith-item h3 {
            margin-top: 0;
            color: #007bff;
        }
        .isnad-chain {
            margin-top: 15px;
        }
        .isnad-chain h4 {
            margin-bottom: 10px;
            color: #555;
        }
        .narrator {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            background-color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .narrator:hover {
            background-color: #e9ecef;
        }
        .narrator strong {
            color: #007bff;
        }
        .bio-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .bio-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }
        .close-button {
            color: #aaa;
            float: left; /* Align to the left for RTL */
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .disclaimer {
            margin-top: 30px;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            background-color: #f8f9fa;
            font-size: 0.9em;
            color: #666;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        .admin-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .admin-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .admin-table td .actions a,
        .admin-table td .actions button {
            margin-left: 5px; /* Adjust margin for RTL */
        }
        .admin-table td .actions button {
             padding: 5px 10px;
             font-size: 0.9em;
        }
         .admin-table td .actions a {
             padding: 5px 10px;
             font-size: 0.9em;
             display: inline-block;
             text-decoration: none;
             color: white;
             background-color: #007bff;
             border-radius: 4px;
         }
         .admin-table td .actions a.btn-danger {
             background-color: #dc3545;
         }
         .admin-table td .actions a:hover {
             opacity: 0.9;
         }
         .admin-form {
             margin-top: 20px;
             padding: 20px;
             border: 1px solid #eee;
             border-radius: 8px;
             background-color: #f9f9f9;
         }
         .admin-form h2 {
             margin-top: 0;
             color: #007bff;
         }
         .backup-restore-section {
             margin-top: 30px;
             padding: 20px;
             border: 1px solid #ccc;
             border-radius: 8px;
             background-color: #f0f2f5;
         }
         .backup-restore-section h3 {
             margin-top: 0;
             color: #555;
         }
         .backup-restore-section form {
             margin-bottom: 15px;
         }
         .backup-restore-section input[type="file"] {
             margin-bottom: 10px;
         }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo lang('app_name'); ?></h1>
        </header>

        <nav>
            <a href="?action=home"><?php echo lang('hadith_list'); ?></a>
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="?action=admin_panel"><?php echo lang('admin_panel'); ?></a>
                <?php endif; ?>
                <a href="?action=logout"><?php echo lang('logout'); ?></a>
            <?php else: ?>
                <a href="?action=login"><?php echo lang('login_button'); ?></a>
            <?php endif; ?>
        </nav>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!is_logged_in()): ?>
            <?php if ($action === 'login'): ?>
                <h2><?php echo lang('login_title'); ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="username"><?php echo lang('username'); ?>:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo lang('password'); ?>:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn"><?php echo lang('login_button'); ?></button>
                </form>
            <?php else: ?>
                 <h2><?php echo lang('welcome_user'); ?></h2>
                 <p><?php echo lang('select_hadith'); ?>:</p>
                 <?php
                 $hadith_list = get_hadith_list();
                 if ($hadith_list): ?>
                     <ul>
                         <?php foreach ($hadith_list as $hadith): ?>
                             <li><a href="?action=view_hadith&id=<?php echo $hadith['id']; ?>"><?php echo htmlspecialchars(mb_substr($hadith['text'], 0, 100, 'UTF-8')) . '...'; ?></a></li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?>
                     <p>کوئی حدیث دستیاب نہیں۔</p>
                 <?php endif; ?>
            <?php endif; ?>

        <?php else: // User is logged in ?>

            <?php if ($action === 'admin_panel' && is_admin()): ?>
                <h2><?php echo lang('admin_panel'); ?></h2>
                <p><a href="?action=admin_hadith"><?php echo lang('manage_hadith'); ?></a></p>
                <p><a href="?action=admin_narrators"><?php echo lang('manage_narrators'); ?></a></p>

                <div class="backup-restore-section">
                    <h3><?php echo lang('backup_db'); ?></h3>
                    <p><a href="?action=backup_db" class="btn"><?php echo lang('backup_db'); ?></a></p>

                    <h3><?php echo lang('restore_db'); ?></h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="restore_db">
                        <div class="form-group">
                            <label for="backup_file"><?php echo lang('select_backup_file'); ?></label>
                            <input type="file" id="backup_file" name="backup_file" accept=".sqlite" required>
                        </div>
                        <button type="submit" class="btn"><?php echo lang('upload_and_restore'); ?></button>
                    </form>
                </div>

            <?php elseif ($action === 'backup_db' && is_admin()): ?>
                 <h2><?php echo lang('backup_db'); ?></h2>
                 <?php
                 $backup_file = backup_database();
                 if ($backup_file) {
                     echo '<div class="message">' . sprintf(lang('backup_success'), $backup_file) . '</div>';
                     echo '<p><a href="' . $backup_file . '" download class="btn">بیک اپ ڈاؤن لوڈ کریں</a></p>';
                 } else {
                     echo '<div class="error">' . lang('backup_failed') . '</div>';
                 }
                 ?>
                 <p><a href="?action=admin_panel">ایڈمن پینل پر واپس جائیں</a></p>


            <?php elseif ($action === 'admin_hadith' && is_admin()): ?>
                <h2><?php echo lang('manage_hadith'); ?></h2>
                <p><a href="?action=add_hadith_form" class="btn"><?php echo lang('add_hadith'); ?></a></p>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo lang('hadith_text'); ?></th>
                            <th><?php echo lang('isnad_label'); ?></th>
                            <th>ایکشنز</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $hadith_list = get_hadith_list();
                        if ($hadith_list): ?>
                            <?php foreach ($hadith_list as $hadith): ?>
                                <tr>
                                    <td><?php echo $hadith['id']; ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($hadith['text'], 0, 150, 'UTF-8')) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($hadith['isnad']); ?></td>
                                    <td class="actions">
                                        <a href="?action=edit_hadith_form&id=<?php echo $hadith['id']; ?>" class="btn">ترمیم</a>
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('<?php echo lang('confirm_delete_hadith'); ?>');">
                                            <input type="hidden" name="action" value="delete_hadith">
                                            <input type="hidden" name="id" value="<?php echo $hadith['id']; ?>">
                                            <button type="submit" class="btn btn-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">کوئی حدیث دستیاب نہیں۔</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'add_hadith_form' && is_admin()): ?>
                <div class="admin-form">
                    <h2><?php echo lang('add_hadith'); ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_hadith">
                        <div class="form-group">
                            <label for="text"><?php echo lang('hadith_text_label'); ?></label>
                            <textarea id="text" name="text" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="isnad"><?php echo lang('isnad_label'); ?></label>
                            <input type="text" id="isnad" name="isnad" placeholder="مثال: 1,2,3,4" required>
                        </div>
                        <button type="submit" class="btn"><?php echo lang('save_hadith'); ?></button>
                        <a href="?action=admin_hadith" class="btn btn-danger"><?php echo lang('cancel'); ?></a>
                    </form>
                </div>

            <?php elseif ($action === 'edit_hadith_form' && is_admin()): ?>
                 <?php
                 $hadith_id = $_GET['id'] ?? 0;
                 $hadith = get_hadith_details($hadith_id);
                 if ($hadith):
                 ?>
                 <div class="admin-form">
                     <h2><?php echo lang('edit_hadith'); ?></h2>
                     <form method="POST">
                         <input type="hidden" name="action" value="update_hadith">
                         <input type="hidden" name="id" value="<?php echo $hadith['id']; ?>">
                         <div class="form-group">
                             <label for="text"><?php echo lang('hadith_text_label'); ?></label>
                             <textarea id="text" name="text" required><?php echo htmlspecialchars($hadith['text']); ?></textarea>
                         </div>
                         <div class="form-group">
                             <label for="isnad"><?php echo lang('isnad_label'); ?></label>
                             <input type="text" id="isnad" name="isnad" value="<?php echo htmlspecialchars($hadith['isnad']); ?>" required>
                         </div>
                         <button type="submit" class="btn"><?php echo lang('save_hadith'); ?></button>
                         <a href="?action=admin_hadith" class="btn btn-danger"><?php echo lang('cancel'); ?></a>
                     </form>
                 </div>
                 <?php else: ?>
                     <div class="error">حدیث نہیں ملی۔</div>
                 <?php endif; ?>

            <?php elseif ($action === 'admin_narrators' && is_admin()): ?>
                <h2><?php echo lang('manage_narrators'); ?></h2>
                <p><a href="?action=add_narrator_form" class="btn"><?php echo lang('add_narrator'); ?></a></p>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo lang('scholar_indx'); ?></th>
                            <th><?php echo lang('narrator_name'); ?></th>
                            <th><?php echo lang('grade'); ?></th>
                            <th>ایکشنز</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $narrators_list = get_narrators();
                        if ($narrators_list): ?>
                            <?php foreach ($narrators_list as $narrator): ?>
                                <tr>
                                    <td><?php echo $narrator['id']; ?></td>
                                    <td><?php echo htmlspecialchars($narrator['scholar_indx']); ?></td>
                                    <td><?php echo htmlspecialchars($narrator['name']); ?></td>
                                    <td><?php echo htmlspecialchars($narrator['grade']); ?></td>
                                    <td class="actions">
                                        <a href="?action=edit_narrator_form&id=<?php echo $narrator['id']; ?>" class="btn">ترمیم</a>
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('<?php echo lang('confirm_delete_narrator'); ?>');">
                                            <input type="hidden" name="action" value="delete_narrator">
                                            <input type="hidden" name="id" value="<?php echo $narrator['id']; ?>">
                                            <button type="submit" class="btn btn-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">کوئی راوی دستیاب نہیں۔</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'add_narrator_form' && is_admin()): ?>
                 <div class="admin-form">
                     <h2><?php echo lang('add_narrator'); ?></h2>
                     <form method="POST">
                         <input type="hidden" name="action" value="add_narrator">
                         <div class="form-group">
                             <label for="scholar_indx"><?php echo lang('scholar_indx'); ?>:</label>
                             <input type="text" id="scholar_indx" name="scholar_indx">
                         </div>
                         <div class="form-group">
                             <label for="name"><?php echo lang('narrator_name'); ?>:</label>
                             <input type="text" id="name" name="name" required>
                         </div>
                         <div class="form-group">
                             <label for="grade"><?php echo lang('grade'); ?>:</label>
                             <input type="text" id="grade" name="grade">
                         </div>
                         <div class="form-group">
                             <label for="parents"><?php echo lang('parents'); ?>:</label>
                             <input type="text" id="parents" name="parents">
                         </div>
                         <div class="form-group">
                             <label for="spouse"><?php echo lang('spouse'); ?>:</label>
                             <input type="text" id="spouse" name="spouse">
                         </div>
                         <div class="form-group">
                             <label for="siblings"><?php echo lang('siblings'); ?>:</label>
                             <input type="text" id="siblings" name="siblings">
                         </div>
                         <div class="form-group">
                             <label for="children"><?php echo lang('children'); ?>:</label>
                             <input type="text" id="children" name="children">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_place"><?php echo lang('birth_date_place'); ?>:</label>
                             <input type="text" id="birth_date_place" name="birth_date_place">
                         </div>
                         <div class="form-group">
                             <label for="places_of_stay"><?php echo lang('places_of_stay'); ?>:</label>
                             <input type="text" id="places_of_stay" name="places_of_stay">
                         </div>
                         <div class="form-group">
                             <label for="death_date_place"><?php echo lang('death_date_place'); ?>:</label>
                             <input type="text" id="death_date_place" name="death_date_place">
                         </div>
                         <div class="form-group">
                             <label for="teachers"><?php echo lang('teachers'); ?>:</label>
                             <input type="text" id="teachers" name="teachers">
                         </div>
                         <div class="form-group">
                             <label for="students"><?php echo lang('students'); ?>:</label>
                             <input type="text" id="students" name="students">
                         </div>
                         <div class="form-group">
                             <label for="area_of_interest"><?php echo lang('area_of_interest'); ?>:</label>
                             <input type="text" id="area_of_interest" name="area_of_interest">
                         </div>
                         <div class="form-group">
                             <label for="tags"><?php echo lang('tags'); ?>:</label>
                             <input type="text" id="tags" name="tags">
                         </div>
                         <div class="form-group">
                             <label for="books"><?php echo lang('books'); ?>:</label>
                             <input type="text" id="books" name="books">
                         </div>
                         <div class="form-group">
                             <label for="students_inds"><?php echo lang('students_inds'); ?>:</label>
                             <input type="text" id="students_inds" name="students_inds">
                         </div>
                         <div class="form-group">
                             <label for="teachers_inds"><?php echo lang('teachers_inds'); ?>:</label>
                             <input type="text" id="teachers_inds" name="teachers_inds">
                         </div>
                         <div class="form-group">
                             <label for="birth_place"><?php echo lang('birth_place'); ?>:</label>
                             <input type="text" id="birth_place" name="birth_place">
                         </div>
                         <div class="form-group">
                             <label for="birth_date"><?php echo lang('birth_date'); ?>:</label>
                             <input type="text" id="birth_date" name="birth_date">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_hijri"><?php echo lang('birth_date_hijri'); ?>:</label>
                             <input type="text" id="birth_date_hijri" name="birth_date_hijri">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_gregorian"><?php echo lang('birth_date_gregorian'); ?>:</label>
                             <input type="text" id="birth_date_gregorian" name="birth_date_gregorian">
                         </div>
                         <div class="form-group">
                             <label for="death_date_hijri"><?php echo lang('death_date_hijri'); ?>:</label>
                             <input type="text" id="death_date_hijri" name="death_date_hijri">
                         </div>
                         <div class="form-group">
                             <label for="death_date_gregorian"><?php echo lang('death_date_gregorian'); ?>:</label>
                             <input type="text" id="death_date_gregorian" name="death_date_gregorian">
                         </div>
                         <div class="form-group">
                             <label for="death_place"><?php echo lang('death_place'); ?>:</label>
                             <input type="text" id="death_place" name="death_place">
                         </div>
                         <div class="form-group">
                             <label for="death_reason"><?php echo lang('death_reason'); ?>:</label>
                             <input type="text" id="death_reason" name="death_reason">
                         </div>
                         <div class="form-group">
                             <label for="bio"><?php echo lang('narrator_bio_label'); ?></label>
                             <textarea id="bio" name="bio"></textarea>
                         </div>
                         <button type="submit" class="btn"><?php echo lang('save_narrator'); ?></button>
                         <a href="?action=admin_narrators" class="btn btn-danger"><?php echo lang('cancel'); ?></a>
                     </form>
                 </div>

            <?php elseif ($action === 'edit_narrator_form' && is_admin()): ?>
                 <?php
                 $narrator_id = $_GET['id'] ?? 0;
                 $narrator = get_narrator_bio($narrator_id); // Using get_narrator_bio to get all fields
                 if ($narrator):
                 ?>
                 <div class="admin-form">
                     <h2><?php echo lang('edit_narrator'); ?></h2>
                     <form method="POST">
                         <input type="hidden" name="action" value="update_narrator">
                         <input type="hidden" name="id" value="<?php echo $narrator['id']; ?>">
                         <div class="form-group">
                             <label for="scholar_indx"><?php echo lang('scholar_indx'); ?>:</label>
                             <input type="text" id="scholar_indx" name="scholar_indx" value="<?php echo htmlspecialchars($narrator['scholar_indx']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="name"><?php echo lang('narrator_name'); ?>:</label>
                             <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($narrator['name']); ?>" required>
                         </div>
                         <div class="form-group">
                             <label for="grade"><?php echo lang('grade'); ?>:</label>
                             <input type="text" id="grade" name="grade" value="<?php echo htmlspecialchars($narrator['grade']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="parents"><?php echo lang('parents'); ?>:</label>
                             <input type="text" id="parents" name="parents" value="<?php echo htmlspecialchars($narrator['parents']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="spouse"><?php echo lang('spouse'); ?>:</label>
                             <input type="text" id="spouse" name="spouse" value="<?php echo htmlspecialchars($narrator['spouse']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="siblings"><?php echo lang('siblings'); ?>:</label>
                             <input type="text" id="siblings" name="siblings" value="<?php echo htmlspecialchars($narrator['siblings']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="children"><?php echo lang('children'); ?>:</label>
                             <input type="text" id="children" name="children" value="<?php echo htmlspecialchars($narrator['children']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_place"><?php echo lang('birth_date_place'); ?>:</label>
                             <input type="text" id="birth_date_place" name="birth_date_place" value="<?php echo htmlspecialchars($narrator['birth_date_place']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="places_of_stay"><?php echo lang('places_of_stay'); ?>:</label>
                             <input type="text" id="places_of_stay" name="places_of_stay" value="<?php echo htmlspecialchars($narrator['places_of_stay']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="death_date_place"><?php echo lang('death_date_place'); ?>:</label>
                             <input type="text" id="death_date_place" name="death_date_place" value="<?php echo htmlspecialchars($narrator['death_date_place']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="teachers"><?php echo lang('teachers'); ?>:</label>
                             <input type="text" id="teachers" name="teachers" value="<?php echo htmlspecialchars($narrator['teachers']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="students"><?php echo lang('students'); ?>:</label>
                             <input type="text" id="students" name="students" value="<?php echo htmlspecialchars($narrator['students']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="area_of_interest"><?php echo lang('area_of_interest'); ?>:</label>
                             <input type="text" id="area_of_interest" name="area_of_interest" value="<?php echo htmlspecialchars($narrator['area_of_interest']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="tags"><?php echo lang('tags'); ?>:</label>
                             <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($narrator['tags']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="books"><?php echo lang('books'); ?>:</label>
                             <input type="text" id="books" name="books" value="<?php echo htmlspecialchars($narrator['books']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="students_inds"><?php echo lang('students_inds'); ?>:</label>
                             <input type="text" id="students_inds" name="students_inds" value="<?php echo htmlspecialchars($narrator['students_inds']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="teachers_inds"><?php echo lang('teachers_inds'); ?>:</label>
                             <input type="text" id="teachers_inds" name="teachers_inds" value="<?php echo htmlspecialchars($narrator['teachers_inds']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="birth_place"><?php echo lang('birth_place'); ?>:</label>
                             <input type="text" id="birth_place" name="birth_place" value="<?php echo htmlspecialchars($narrator['birth_place']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="birth_date"><?php echo lang('birth_date'); ?>:</label>
                             <input type="text" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($narrator['birth_date']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_hijri"><?php echo lang('birth_date_hijri'); ?>:</label>
                             <input type="text" id="birth_date_hijri" name="birth_date_hijri" value="<?php echo htmlspecialchars($narrator['birth_date_hijri']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="birth_date_gregorian"><?php echo lang('birth_date_gregorian'); ?>:</label>
                             <input type="text" id="birth_date_gregorian" name="birth_date_gregorian" value="<?php echo htmlspecialchars($narrator['birth_date_gregorian']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="death_date_hijri"><?php echo lang('death_date_hijri'); ?>:</label>
                             <input type="text" id="death_date_hijri" name="death_date_hijri" value="<?php echo htmlspecialchars($narrator['death_date_hijri']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="death_date_gregorian"><?php echo lang('death_date_gregorian'); ?>:</label>
                             <input type="text" id="death_date_gregorian" name="death_date_gregorian" value="<?php echo htmlspecialchars($narrator['death_date_gregorian']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="death_place"><?php echo lang('death_place'); ?>:</label>
                             <input type="text" id="death_place" name="death_place" value="<?php echo htmlspecialchars($narrator['death_place']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="death_reason"><?php echo lang('death_reason'); ?>:</label>
                             <input type="text" id="death_reason" name="death_reason" value="<?php echo htmlspecialchars($narrator['death_reason']); ?>">
                         </div>
                         <div class="form-group">
                             <label for="bio"><?php echo lang('narrator_bio_label'); ?></label>
                             <textarea id="bio" name="bio"><?php echo htmlspecialchars($narrator['bio']); ?></textarea>
                         </div>
                         <button type="submit" class="btn"><?php echo lang('save_narrator'); ?></button>
                         <a href="?action=admin_narrators" class="btn btn-danger"><?php echo lang('cancel'); ?></a>
                     </form>
                 </div>
                 <?php else: ?>
                     <div class="error">راوی نہیں ملا۔</div>
                 <?php endif; ?>

            <?php elseif ($action === 'view_hadith'): ?>
                <?php
                $hadith_id = $_GET['id'] ?? 0;
                $hadith = get_hadith_details($hadith_id);
                if ($hadith):
                ?>
                    <div class="hadith-item">
                        <h3><?php echo lang('hadith_text'); ?>:</h3>
                        <p><?php echo htmlspecialchars($hadith['text']); ?></p>

                        <div class="isnad-chain">
                            <h4><?php echo lang('isnad_chain'); ?>:</h4>
                            <?php if (!empty($hadith['isnad_narrators'])): ?>
                                <?php foreach ($hadith['isnad_narrators'] as $narrator): ?>
                                    <div class="narrator" data-narrator-id="<?php echo $narrator['id']; ?>">
                                        <strong><?php echo htmlspecialchars($narrator['name']); ?></strong> (<?php echo htmlspecialchars($narrator['grade']); ?>)
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>اس حدیث کے لیے کوئی اسناد دستیاب نہیں۔</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="bioModal" class="bio-modal">
                        <div class="bio-modal-content">
                            <span class="close-button">×</span>
                            <h3 id="bioModalTitle"><?php echo lang('narrator_bio'); ?></h3>
                            <div id="bioModalContent">
                                <!-- Bio content will be loaded here -->
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="error">حدیث نہیں ملی۔</div>
                <?php endif; ?>

            <?php else: // Default home page for logged-in users ?>
                <h2><?php echo lang('welcome_user'); ?></h2>
                <p><?php echo lang('select_hadith'); ?>:</p>
                <?php
                $hadith_list = get_hadith_list();
                if ($hadith_list): ?>
                    <ul>
                        <?php foreach ($hadith_list as $hadith): ?>
                            <li><a href="?action=view_hadith&id=<?php echo $hadith['id']; ?>"><?php echo htmlspecialchars(mb_substr($hadith['text'], 0, 100, 'UTF-8')) . '...'; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>کوئی حدیث دستیاب نہیں۔</p>
                <?php endif; ?>

            <?php endif; // End of logged-in user actions ?>

        <?php endif; // End of login check ?>

        <div class="disclaimer">
            <h4><?php echo lang('disclaimer_title'); ?></h4>
            <p><?php echo lang('disclaimer_text'); ?></p>
        </div>

    </div>

    <script>
        // JavaScript for modal and AJAX bio loading
        var bioModal = document.getElementById("bioModal");
        var span = document.getElementsByClassName("close-button")[0];
        var bioModalContent = document.getElementById("bioModalContent");

        document.querySelectorAll('.narrator').forEach(item => {
            item.addEventListener('click', event => {
                const narratorId = event.target.closest('.narrator').dataset.narratorId;
                if (narratorId) {
                    fetch('?action=get_narrator_bio&id=' + narratorId)
                        .then(response => response.json())
                        .then(data => {
                            if (data) {
                                let bioHtml = `
                                    <p><strong>${'<?php echo lang('narrator_name'); ?>'}</strong> ${data.name}</p>
                                    <p><strong>${'<?php echo lang('grade'); ?>'}</strong> ${data.grade || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('parents'); ?>'}</strong> ${data.parents || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('spouse'); ?>'}</strong> ${data.spouse || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('siblings'); ?>'}</strong> ${data.siblings || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('children'); ?>'}</strong> ${data.children || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('birth_date_place'); ?>'}</strong> ${data.birth_date_place || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('places_of_stay'); ?>'}</strong> ${data.places_of_stay || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('death_date_place'); ?>'}</strong> ${data.death_date_place || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('teachers'); ?>'}</strong> ${data.teachers || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('students'); ?>'}</strong> ${data.students || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('area_of_interest'); ?>'}</strong> ${data.area_of_interest || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('tags'); ?>'}</strong> ${data.tags || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('books'); ?>'}</strong> ${data.books || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('birth_place'); ?>'}</strong> ${data.birth_place || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('birth_date'); ?>'}</strong> ${data.birth_date || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('birth_date_hijri'); ?>'}</strong> ${data.birth_date_hijri || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('birth_date_gregorian'); ?>'}</strong> ${data.birth_date_gregorian || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('death_date_hijri'); ?>'}</strong> ${data.death_date_hijri || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('death_date_gregorian'); ?>'}</strong> ${data.death_date_gregorian || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('death_place'); ?>'}</strong> ${data.death_place || 'NA'}</p>
                                    <p><strong>${'<?php echo lang('death_reason'); ?>'}</strong> ${data.death_reason || 'NA'}</p>
                                    <p><strong><?php echo lang('narrator_bio_label'); ?></strong> ${data.bio || '<?php echo lang('no_bio_available'); ?>'}</p>
                                `;
                                bioModalContent.innerHTML = bioHtml;
                                bioModal.style.display = "block";
                            } else {
                                bioModalContent.innerHTML = '<p><?php echo lang('no_bio_available'); ?></p>';
                                bioModal.style.display = "block";
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching narrator bio:', error);
                            bioModalContent.innerHTML = '<p>Error loading bio.</p>';
                            bioModal.style.display = "block";
                        });
                }
            });
        });

        span.onclick = function() {
            bioModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == bioModal) {
                bioModal.style.display = "none";
            }
        }
    </script>

    <?php
    // Handle AJAX request for narrator bio
    if (isset($_GET['action']) && $_GET['action'] === 'get_narrator_bio' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $narrator_id = $_GET['id'];
        $bio = get_narrator_bio($narrator_id);
        echo json_encode($bio);
        exit; // Stop further PHP execution
    }
    ?>

</body>
</html><script>
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