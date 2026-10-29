<?php
$xmlFile = 'qlsv.xml';
$xml = file_exists($xmlFile) ? simplexml_load_file($xmlFile) : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xem danh sách</title>
    <link rel="stylesheet" href="giaodien.css">
</head>
<body>
    <a href="trangchu.php">Quay lại trang chủ</a>
    <h3 href="trangchu.php">Danh sách sinh viên</h3>
    <table>
        <tr>
            <th>STT</th>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Ngày sinh</th>
            <th>Thành phố</th>
        </tr>
        <?php
        if ($xml && count($xml->student) > 0) {
            $stt = 1;
            foreach ($xml->student as $s) {
                echo "<tr>";
                echo "<td class='center'>{$stt}</td>";
                echo "<td class='center'>{$s->id}</td>";
                echo "<td>{$s->name}</td>";
                echo "<td class='center'>{$s->date}</td>";
                echo "<td>{$s->city}</td>";
                echo "</tr>";
                $stt++;
            }
        } else {
            echo "<tr><td colspan='5' style='text-align: center;'>Chưa có dữ liệu sinh viên!</td></tr>";
        }
        ?>
    </table>
</body>
</html>