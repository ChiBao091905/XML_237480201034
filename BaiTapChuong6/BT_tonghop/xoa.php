<?php
$xmlFile = 'qlsv.xml';
$message = '';

if (isset($_GET['id'])) {
    $deleteId = $_GET['id'];
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($xmlFile);

    $studentsList = $dom->getElementsByTagName('students')->item(0);
    $students = $dom->getElementsByTagName('student');
    $deleted = false;

    for ($i = $students->length - 1; $i >= 0; $i--) {
        $student = $students->item($i);
        $idNode = $student->getElementsByTagName('id')->item(0);
        if ($idNode && trim($idNode->nodeValue) == $deleteId) {
            $studentsList->removeChild($student);
            $deleted = true;
        }
    }

    if ($deleted) {
        $dom->save($xmlFile);
        $message = "Đã xóa thành công sinh viên có ID: $deleteId!";
    }
}

$xml = file_exists($xmlFile) ? simplexml_load_file($xmlFile) : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xóa sinh viên</title>
    <link rel="stylesheet" href="giaodien.css">
</head>
<body>
    <div class="box">
        <div class="trai">
            <h3>Xóa sinh viên khỏi danh sách</h3>
            <p style="color: red;"><?php echo $message; ?></p>
            <table>
                <tr>
                    <th>ID</th><th>Họ tên</th><th>Thao tác</th>
                </tr>
                <?php
                if ($xml && count($xml->student) > 0) {
                    foreach ($xml->student as $s) {
                        echo "<tr>";
                        echo "<td>{$s->id}</td>";
                        echo "<td>{$s->name}</td>";
                        echo "<td style='text-align: center;'><a class='delete-btn' href='delete.php?id={$s->id}' onclick=\"return confirm('Bạn có chắc muốn xóa sinh viên này?');\">Xóa</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align: center;'>Không có sinh viên nào.</td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="phai">
            <?php include 'danhsach.php'; ?>
        </div>
    </div>
    
</body>
</html>
