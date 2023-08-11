<?php
// 自訂 JSON 回應格式
$response = array(
  "status" => "fail",
  "code" => 400,
  "message" => "",
  "data" => new stdClass() // 如有其他回傳資料可以用關聯陣列放在 data 中
);

/*
使用範例：
$response['status'] = 'success';
$response['code'] = 200;
$response['message'] = '請求成功';
$response['data'] = array(
  'key1' => 'value1',
  'key2' => 'value2'
);

最終將輸出：
{
  "status": "success",
  "code": 200,
  "message": "請求成功",
  "data": {
    "key1": "value1",
    "key2": "value2"
  }
}
*/
?>