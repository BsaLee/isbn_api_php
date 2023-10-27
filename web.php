<!DOCTYPE html>
<html>
<head>
    <title>PDD书籍ISBN采集</title>
    <style>
        .container {
            width: 98%; /* 设置表格容器的宽度 */
            margin: 0 auto; /* 居中显示 */
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }

        th {
            background-color: green;
            color: white;
        }

        tr:nth-child(even) {
            background-color: lightgray;
        }

        .export-button {
            position: fixed;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDD书籍ISBN采集</h1>

        <?php
        // 连接到数据库
        include 'db.php';

        $conn = new mysqli($servername, $username, $password, $dbname);

        // 检查连接是否成功
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }

        // 查询数据总数
        $countSql = "SELECT COUNT(*) AS total FROM data";
        $countResult = $conn->query($countSql);
        $totalCount = $countResult->fetch_assoc()["total"];

        // 每页显示的数据量
        $pageSize = 10;

        // 当前页码（从 URL 参数中获取）
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

        // 计算总页数
        $totalPages = ceil($totalCount / $pageSize);

        // 确保当前页码在有效范围内
        if ($currentPage < 1) {
            $currentPage = 1;
        } elseif ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        // 计算当前页的起始索引
        $startIndex = ($currentPage - 1) * $pageSize;

        // 查询当前页的数据
        $sql = "SELECT ISBN, time, author, logo, publisher, published, title FROM data ORDER BY time DESC LIMIT $startIndex, $pageSize";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>ISBN</th><th>时间</th><th>作者</th><th>Logo</th><th>出版社</th><th>出版日期</th><th>标题</th></tr></thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td style='width: 10%;'>" . $row["ISBN"] . "</td>";
                echo "<td style='width: 17%;'>" . $row["time"] . "</td>";
                echo "<td style='width: 15%;'>" . $row["author"] . "</td>";
                echo "<td style='width: 15%;'><a href='" . $row["logo"] . "' target='_blank'>" . $row["logo"] . "</a></td>";
                echo "<td style='width: 15%;'>" . $row["publisher"] . "</td>";
                echo "<td style='width: 9%;'>" . $row["published"] . "</td>";
                echo "<td style='width: 20%;'>" . $row["title"] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";

            // 显示翻页链接
            echo "<br>";
            echo "<div>";
            echo "共 " . $totalCount . " 条记录，当前第 " . $currentPage . " 页，共 " . $totalPages . " 页：";
            echo "<br>";
            if ($currentPage > 1) {
                echo "<a href='?page=" . ($currentPage - 1) . "'>上一页</a> ";
            }
            if ($currentPage < $totalPages) {
                echo "<a href='?page=" . ($currentPage + 1) . "'>下一页</a> ";
            }
            echo "</div>";
        } else {
            echo "<p>无数据</p>";
        }

        // 查询最后一条数据的时间
        $lastUpdateTimeSql = "SELECT time FROM data ORDER BY time DESC LIMIT 1";
        $lastUpdateTimeResult = $conn->query($lastUpdateTimeSql);
        $lastUpdateTime = $lastUpdateTimeResult->fetch_assoc()["time"];

        // 导出数据
        $exportSql = "SELECT ISBN, time, author, logo, publisher, published, title FROM data";
        $exportResult = $conn->query($exportSql);

        if ($exportResult->num_rows > 0) {
            // 创建一个CSV文件
            $filename = "data.csv";
            $file = fopen($filename, 'w');

            // 写入表头
            $header = array("ISBN", "时间", "作者", "Logo", "出版社", "出版日期", "标题");
            fputcsv($file, $header);

            // 写入数据
            while ($row = $exportResult->fetch_assoc()) {
                $data = array($row["ISBN"], $row["time"], $row["author"], $row["logo"], $row["publisher"], $row["published"], $row["title"]);
                fputcsv($file, $data);
            }

            // 关闭文件
            fclose($file);

            // 提示下载链接
            echo "<br>";
            echo "<button class='export-button' onclick='downloadData()'>导出数据</button>";
        }

        // 关闭数据库连接
        $conn->close();
        ?>

        <br>
        <br>
        <p>数据最后更新时间：<?php echo $lastUpdateTime; ?></p>

        <script>
            function downloadData() {
                window.location.href = "data.csv";
            }
        </script>
    </div>
</body>
</html>
