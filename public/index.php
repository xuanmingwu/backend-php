<?php
$allowed_domains = ['https://www.0702book.com', 'https://0702book.com'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 獲取請求的路徑
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

// 過濾輸入以增強安全性
$allowed_paths = ['/', '/AllenAPI/sign', '/AllenAPI/login'];
if (!in_array($request_uri[0], $allowed_paths)) {
  header('HTTP/1.0 404 Not Found');
  echo json_encode(["result" => false, "errorCode" => "Page not found"]);
  exit;
}

switch ($request_uri[0]) {
  case '/':
    header("Content-Type: text/html; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    require 'home.php';
    break;
  case '/AllenAPI/sign':
    require '../sign.php';
    break;
  case '/AllenAPI/login':
    require '../login.php';
    break;
  default:
    header('HTTP/1.0 404 Not Found');
    echo json_encode(["result" => false, "errorCode" => "Page not found"]);
    break;
}
?>