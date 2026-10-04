<?php
// ==============================================================
// Cấu hình kết nối Database
// ==============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'QL_BANMT');
define('DB_CHARSET', 'utf8mb4');

function getDB()
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Kết nối thất bại: ' . $conn->connect_error]));
        }
        $conn->set_charset(DB_CHARSET);
        $conn->query("SET NAMES utf8mb4");
   
        $conn->query("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
    }
    return $conn;
}

// Hàm helper query an toàn
function dbQuery($sql, $params = [], $types = '')
{
    $conn = getDB();
    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log("SQL Error: " . $conn->error . " | Query: " . $sql);
            return false;
        }
        return $result;
    }
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare Error: " . $conn->error);
        return false;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function dbFetch($sql, $params = [], $types = '')
{
    $result = dbQuery($sql, $params, $types);
    if (!$result) return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function dbFetchOne($sql, $params = [], $types = '')
{
    $result = dbQuery($sql, $params, $types);
    if (!$result) return null;
    return $result->fetch_assoc();
}

function dbExecute($sql, $params = [], $types = '')
{
    $conn = getDB();
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
