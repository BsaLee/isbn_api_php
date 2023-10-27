<?php
// 连接数据库
$servername = "localhost";
$username = "xxxxx";
$password = "xxxxx";
$dbname = "xxxxx";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("连接数据库失败：" . $conn->connect_error);
}
?>
