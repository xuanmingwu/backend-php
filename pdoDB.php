<?php
require_once __DIR__ . '/../config.php';

class pdoDB
{
  private $pdo;
  private $inTransaction = false;

  // 構造函式 ( 建立此 class 就會自動執行，建立與資料庫連線 )
  public function __construct()
  {
    $host = getenv('DB_HOST');
    $databaseUsername = getenv('DB_USERNAME');
    $databasePassword = getenv('DB_PASSWORD');
    $db = getenv('DB_NAME');
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
      $this->pdo = new PDO($dsn, $databaseUsername, $databasePassword, $opt);
    } catch (PDOException $e) {
      die("ERROR: Could not connect. " . $e->getMessage());
    }
  }
  // 執行查詢
  public function pdoQuery($query, $parameters = [])
  {
    try {
      $stmt = $this->pdo->prepare($query);
      $stmt->execute($parameters);
      return $stmt;
    } catch (PDOException $e) {
      if ($this->inTransaction) {
        $this->pdo->rollback();
      }
      throw $e;
    }
  }

  // 獲取查詢的結果行數
  public function pdoNumRows($stmt)
  {
    return $stmt->rowCount();
  }

  // 獲取查詢影響的行數
  public function pdoAffectedRows($stmt)
  {
    return $stmt->rowCount();
  }

  // 獲取查詢結果的欄位數
  public function pdoNumFields($stmt)
  {
    return $stmt->columnCount();
  }

  // 獲取查詢結果的特定欄位名稱，輸入的 $column_number 為欄位的索引
  public function pdoFieldName($stmt, $column_number)
  {
    $meta = $stmt->getColumnMeta($column_number);
    return $meta['name'];
  }

  // 從查詢結果中獲取一行資料，並將其以關聯陣列的方式回傳
  public function pdoFetchRow($stmt)
  {
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // 從查詢結果中獲取所有行的資料，並將其以關聯陣列的方式回傳
  public function pdoFetchRowset($stmt)
  {
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // 從查詢結果中獲取一行資料中指定欄位的值，欄位名稱由 $field 指定
  public function pdoFetchField($stmt, $field)
  {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row[$field];
  }

  // 獲取資料庫中最後一筆新增的 ID
  public function pdoNextId()
  {
    return $this->pdo->lastInsertId();
  }

  // 釋放查詢結果，用以釋放佔用的記憶體
  public function pdoFreeResult($stmt)
  {
    $stmt = null;
    $this->pdo = null;
  }

  // 獲取查詢錯誤的訊息和代碼
  public function pdoError($stmt)
  {
    $errorInfo = $stmt->errorInfo();
    return ['message' => $errorInfo[2], 'code' => $stmt->errorCode()];
  }

  // 開始一個資料庫交易
  public function beginTransaction()
  {
    $this->inTransaction = $this->pdo->beginTransaction();
  }

  // 提交一個資料庫交易
  public function commit()
  {
    if ($this->inTransaction) {
      $this->pdo->commit();
      $this->inTransaction = false;
    }
  }

  // 回滾一個資料庫交易
  public function rollback()
  {
    if ($this->inTransaction) {
      $this->pdo->rollback();
      $this->inTransaction = false;
    }
  }
}
?>