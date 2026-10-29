<?php
$xmlFile = 'qlsv.xml';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $targetId  = trim($_POST['id'] ?? '');
    $newName   = trim($_POST['name'] ?? '');
    $newDob    = trim($_POST['dob'] ?? '');     // Nhận dữ liệu nhập tay
    $newCity   = trim($_POST['city'] ?? '');

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    
    if (file_exists($xmlFile)) {
        $dom->load($xmlFile);

        $updated = false;
        foreach ($dom->getElementsByTagName('student') as $student) {
            $idNode = $student->getElementsByTagName('id')->item(0);
            if ($idNode && trim($idNode->nodeValue) == $targetId) {
                // Cập nhật Họ tên
                $nameNode = $student->getElementsByTagName('name')->item(0);
                if ($nameNode) {
                    $nameNode->nodeValue = htmlspecialchars($newName);
                }

                // Cập nhật Ngày sinh
                $dobNode = $student->getElementsByTagName('dob')->item(0);
                if ($dobNode) {
                    $dobNode->nodeValue = htmlspecialchars($newDob);
                } else {
                    $newDobElement = $dom->createElement('dob', htmlspecialchars($newDob));
                    $student->appendChild($newDobElement);
                }

                // Cập nhật Thành phố
                $cityNode = $student->getElementsByTagName('city')->item(0);
                if ($cityNode) {
                    $cityNode->nodeValue = htmlspecialchars($newCity);
                } else {
                    $newCityElement = $dom->createElement('city', htmlspecialchars($newCity));
                    $student->appendChild($newCityElement);
                }

                $updated = true;
                break;
            }
        }

        if ($updated) {
            $dom->save($xmlFile);
            $message = "Cập nhật thành công cho sinh viên ID: $targetId!";
        } else {
            $message = "Không tìm thấy sinh viên có ID: $targetId!";
        }
    } else {
        $message = "Không tìm thấy file dữ liệu qlsv.xml!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật sinh viên</title>
    <link rel="stylesheet" href="giaodien.css">
</head>
<body>
    <div class="box">
        <div class="trai">
            <h3>Cập nhật thông tin sinh viên</h3>
            <p style="color: blue;"><?php echo $message; ?></p>
            
            <form method="POST">
                Nhập ID sinh viên cần sửa: <br>
                <input type="text" name="id" required><br><br>
                
                Họ tên mới: <br>
                <input type="text" name="name" required><br><br>

                Ngày sinh mới <br>
                <input type="text" name="dob" br><br><br>

                Thành phố mới: <br>
                <input type="text" name="city" required><br><br>

                <button type="submit">Cập nhật</button>
            </form>
        </div>

        <div class="phai">
            <?php include 'danhsach.php'; ?>
        </div>
    </div>
    
</body>
</html>
