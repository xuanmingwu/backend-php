<?php

require_once 'pdoDB.php';
// 引用 JSON 回應格式
require_once 'response_template.php';


$allowed_domains = ['https://www.0702book.com', 'https://0702book.com'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$pdoDB = new pdoDB();

$data = json_decode(file_get_contents('php://input'));

// 檢查是否符合 API 設定必填項目
if (
  $data->action == 'sign' &&
  !empty($data->parameters->name) &&
  !empty($data->parameters->email) &&
  !empty($data->parameters->password)
) {
  $emailQuery = 'SELECT id FROM users WHERE email=:email';
  $emailStmt = $pdoDB->pdoQuery($emailQuery, [':email' => $data->parameters->email]);

  if ($emailStmt->rowCount() > 0) {
    $response["code"] = 400;
    $response["message"] = "電子郵件已存在。";
  } else {
    try {
      $query = 'INSERT INTO users SET name=:name, email=:email, password=:password';
      $stmt = $pdoDB->pdoQuery($query, [
        ':name' => $data->parameters->name,
        ':email' => $data->parameters->email,
        ':password' => password_hash($data->parameters->password, PASSWORD_BCRYPT),
      ]);

      if ($stmt->rowCount() > 0) {
        $response["status"] = "success";
        $response["code"] = 201;
        $response["message"] = "用戶已創建。";
      } else {
        $response["code"] = 503;
        $response["message"] = "無法創建用戶。";
      }
    } catch (PDOException $exception) {
      $response["code"] = 500;
      $response["message"] = "嘗試創建用戶時發生錯誤。";
    }
  }
} else {
  $response["code"] = 400;
  $response["message"] = "無法創建用戶。資料不完整。";
}

http_response_code($response["code"]);
echo json_encode($response);
?>