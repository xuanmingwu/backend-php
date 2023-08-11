<?php
require_once 'pdoDB.php';
require_once 'jwt_handler.php'; // 引用 JWT 操作方法檔案

$allowed_domains = ['https://www.0702book.com', 'https://0702book.com'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 引用 JSON 回應格式
require_once 'response_template.php';

// 接收 JSON 格式的請求
$data = json_decode(file_get_contents("php://input"));

// 檢查是否符合 API 設定必填項目
if (
  $data->action === "login" &&
  !empty($data->parameters->email) &&
  !empty($data->parameters->password)
) {
  try {
    $pdoDB = new pdoDB();
    $query = 'SELECT * FROM users WHERE email = :email';
    $stmt = $pdoDB->pdoQuery($query, [':email' => $data->parameters->email]);

    if ($pdoDB->pdoNumRows($stmt) > 0) {
      $row = $pdoDB->pdoFetchRow($stmt);
      $password_hash = $row['password'];
      $failed_attempts = $row['failed_attempts'];
      $last_failed_at = $row['last_failed_at'];
      // 如果密碼錯誤超過五次， 30 分鐘內不允許登入
      if ($failed_attempts >= 5 && $last_failed_at && strtotime($last_failed_at) > strtotime('-30 minutes')) {
        $response["code"] = 429;
        $response["message"] = "嘗試次數過多。請等待30分鐘後再試。";
      } else {
        if (password_verify($data->parameters->password, $password_hash)) {
          $update_query = 'UPDATE users SET last_login_at = NOW(), failed_attempts = 0, last_failed_at = NULL WHERE email = :email';
          $pdoDB->pdoQuery($update_query, [':email' => $data->parameters->email]);

          // 創建 JWT
          $jwt = createJWT(["email" => $data->parameters->email]);

          // 將 JWT 加到 HTTP 頭部傳給用戶
          header("Authorization: Bearer " . $jwt);
          $response["status"] = "success";
          $response["code"] = 200;
          $response["message"] = "登入成功。";
        } else {
          $update_query = 'UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_at = NOW() WHERE email = :email';
          $pdoDB->pdoQuery($update_query, [':email' => $data->parameters->email]);

          $response["code"] = 401;
          $response["message"] = "無效的密碼。";
        }
      }
    } else {
      $response["code"] = 404;
      $response["message"] = "找不到使用者。";
    }
  } catch (PDOException $exception) {
    $response["code"] = 500;
    $response["message"] = "嘗試登錄時發生錯誤。";
  }
} else {
  $response["code"] = 400;
  $response["message"] = "無法登錄。資料不完整。";
}

http_response_code($response["code"]);
echo json_encode($response);
?>