<?php
// 连接数据库
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("连接数据库失败：" . $conn->connect_error);
}

// 处理 GET 请求
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // 检查是否存在 data 参数
    if (isset($_GET["data"])) {
        $data = $_GET["data"];

        // 提取连续的 13 位或 10 位数字
        preg_match('/\d{13}|\d{10}/', $data, $matches);
        if (isset($matches[0])) {
            $isbn = $matches[0];

            // 查询数据库是否存在相同的 ISBN
            $sql = "SELECT * FROM data WHERE isbn = '$isbn'";
            $result = $conn->query($sql);

            if ($result->num_rows == 0) {
                // 不存在相同的 ISBN，将数据插入数据库
                $time = date("Y-m-d H:i:s");
                $sql = "INSERT INTO data (isbn, time) VALUES ('$isbn', '$time')";
                if ($conn->query($sql) === TRUE) {
                    // 执行 GET 请求获取数据
                    $url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/isbn.php?isbn=$isbn&data=$data";
                    $json = file_get_contents($url);
                    $data = json_decode($json, true);

                    // 检查返回的 JSON 数据是否包含所需字段
                    if (isset($data['title']) && isset($data['author']) && isset($data['logo']) && isset($data['publisher'])) {
                        $title = $data['title'];
                        $author = $data['author'][0]['name'];
                        $logo = $data['logo'];
                        $publisher = $data['publisher'];

                        // 处理 publisher 字段数据
                        $publisher = preg_replace('/\s+/', '', $publisher); // 删除空格和换行
                        $publisher = strip_tags($publisher); // 删除 HTML 标签
                        $publisher = preg_replace('/(出品方|副标题|出品年|出版年|ISBN|定价).*/u', '', $publisher); // 删除 '出品方'、'副标题'、'出品年' 及其之后的内容

                        $published = trim($data['published']);

                        // 更新数据库中的数据
                        $sql = "UPDATE data SET title = '$title', author = '$author', logo = '$logo', publisher = '$publisher', published = '$published' WHERE isbn = '$isbn'";
                        if ($conn->query($sql) === TRUE) {
                            echo "数据插入成功";
                        } else {
                            echo "数据插入失败：" . $conn->error;
                        }
                    } else {
                        // JSON 数据不包含所需字段，仅插入 ISBN
                        echo "返回的 JSON 数据不包含所需字段，仅插入 ISBN";
                    }
                } else {
                    echo "数据插入失败：" . $conn->error;
                }
            } else {
                // 存在相同的 ISBN，放弃处理
                echo "已存在相同的 ISBN，放弃处理";
            }
        } else {
            echo "参数中不包含连续的 13 位或 10 位数字";
        }
    } else {
        echo "缺少 data 参数";
    }
}

$conn->close();
?>
