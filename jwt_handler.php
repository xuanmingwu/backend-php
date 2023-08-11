<?php
require_once __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
輸入內容的格式說明：
$userData = [
  "email" => "john.doe@example.com",
];
*/

function createJWT($userData)
{
  $key = "test0702";
  
  $token_payload = [
    "iss" => "https://www.0702book.com", // 發行者
    "aud" => "https://www.0702book.com", // 此 JWT 目標使用者或服務
    "iat" => time(), // 發行時間
    "exp" => time() + (60 * 60), // 過期時間 ( 一小時過期 )
    "data" => $userData
  ];
  $jwt = JWT::encode($token_payload, $key, "HS256");
  return $jwt;
}

function decodeJWT($jwt)
{
  $key = "test0702";
  try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $decoded_array = json_decode(json_encode($decoded), true);
    return $decoded_array['data']['email']; // 返回解碼後的信箱字串
  } catch (\Exception $e) {
    // JWT 解碼失敗，例如過期或密鑰不匹配
    return false;
  }
}
?>