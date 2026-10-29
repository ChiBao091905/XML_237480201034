<?php
$xmlFile = 'qlsv.xml';
$totalStudents = 0;
$cityStats = [];

if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    $totalStudents = count($xml->student);
    foreach ($xml->student as $s) {
        $city = trim($s->city);
        if (!empty($city)) {
            if (!isset($cityStats[$city])) {
                $cityStats[$city] = 0;
            }
            $cityStats[$city]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê sinh viên</title>
    <link rel="stylesheet" href="giaodien.css">
</head>
<body>
    <div class="box">
        <div class="trai">
            <h3>Thống kê danh sách sinh viên</h3>
            <p><b>Tổng số sinh viên:</b> <?php echo $totalStudents; ?></p>
            
            <h4>Thống kê theo Thành phố:</h4>
            <ul>
                <?php
                if (count($cityStats) > 0) {
                    foreach ($cityStats as $city => $count) {
                        echo "<li><b>{$city}:</b> {$count} sinh viên</li>";
                    }
                } else {
                    echo "<li>Chưa có dữ liệu thống kê.</li>";
                }
                ?>
            </ul>
        </div>

        <div class="phai">
            <?php include 'danhsach.php'; ?>
        </div>
    </div>
    
</body>
</html>
