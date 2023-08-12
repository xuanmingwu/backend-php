<?php
require 'vendor/autoload.php';

use \Firebase\JWT\JWT;

// 跨域設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 檢查請求方法是否為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode(["result" => false, "error" => "Method not allowed"]);
    exit;
}

// 讀取 POST 資料
$inputData = json_decode(file_get_contents("php://input"), true);

// 檢查是否傳入 JWT 令牌
if (!isset($inputData['jwt_token'])) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(["result" => false, "error" => "JWT token is missing"]);
    exit;
}

// 解碼 JWT 令牌並檢查是否有效
$jwtToken = $inputData['jwt_token'];
$validToken = validateToken($jwtToken);
if (!$validToken) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(["result" => false, "error" => "Invalid or expired token"]);
    exit;
}

// 檢查是否傳入需要儲存的資料
if (!isset($inputData['core_data'])) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(["result" => false, "error" => "Core Data is missing"]);
    exit;
}

// 傳入資料庫儲存資料
$success = saveCoreDataToDatabase($inputData['core_data']);

if ($success) {
    echo json_encode(["result" => true, "message" => "Data saved successfully"]);
} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode(["result" => false, "error" => "Failed to save data"]);
}

// JWT 令牌驗證
function validateToken($jwtToken) {
    try {
        $jwtSecret = 'test0702'; // 替換為您的 JWT 密鑰
        $decoded = JWT::decode($jwtToken, $jwtSecret, array('HS256'));
        
        // 在這裡可以進一步檢查其他 JWT 資訊，例如發行者、目標使用者等
        
        return true; // 令牌有效
    } catch (Exception $e) {
        return false; // 解碼失敗或令牌無效
    }
}

// 資料儲存到資料庫
function saveCoreDataToDatabase($coreData) {
    $host = 'your_database_host';
    $dbname = 'your_database_name';
    $username = 'your_database_username';
    $password = 'your_database_password';

    try {
        // 連接資料庫
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 將 Core Data 資料插入資料庫表格
        $stmt = $pdo->prepare("INSERT INTO core_data_table (column1, column2) VALUES (:value1, :value2)");

        // 設定要插入的值
        $stmt->bindParam(':value1', $coreData['value1']);
        $stmt->bindParam(':value2', $coreData['value2']);

        // 執行 SQL 語句
        $success = $stmt->execute();

        // 返回是否成功
        return $success;
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
        return false;
    }
}
?>

