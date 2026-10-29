<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Cấu hình Database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "danhba_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Kết nối CSDL thất bại: " . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8mb4");

$xmlFilePath = __DIR__ . "/contacts.xml";

// Hàm hỗ trợ đồng bộ dữ liệu MySQL ra file contacts.xml
function syncDatabaseToXML($conn, $filePath) {
    $result = $conn->query("SELECT * FROM contacts ORDER BY id ASC");
    
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;
    
    $root = $xml->createElement("contacts");
    $xml->appendChild($root);
    
    while ($row = $result->fetch_assoc()) {
        $contactNode = $xml->createElement("contact");
        $contactNode->setAttribute("id", $row['id']);
        
        $name = $xml->createElement("name", htmlspecialchars($row['name']));
        $phone = $xml->createElement("phone", htmlspecialchars($row['phone']));
        $email = $xml->createElement("email", htmlspecialchars($row['email']));
        $group = $xml->createElement("group", htmlspecialchars($row['contact_group']));
        
        $contactNode->appendChild($name);
        $contactNode->appendChild($phone);
        $contactNode->appendChild($email);
        $contactNode->appendChild($group);
        
        $root->appendChild($contactNode);
    }
    
    $xml->save($filePath);
}

// ----------------------------------------------------
// 1. NHẬP DỮ LIỆU TỪ FILE XML VÀO CSDL (IMPORT XML)
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'import_xml') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Vui lòng chọn file XML hợp lệ."]);
        exit();
    }

    $uploadedFile = $_FILES['xml_file']['tmp_name'];
    $xml = simplexml_load_file($uploadedFile);

    if ($xml === false) {
        echo json_encode(["status" => "error", "message" => "Cấu trúc file XML không hợp lệ."]);
        exit();
    }

    $count = 0;
    foreach ($xml->contact as $item) {
        $name = $conn->real_escape_string((string)$item->name);
        $phone = $conn->real_escape_string((string)$item->phone);
        $email = $conn->real_escape_string((string)$item->email);
        $group = $conn->real_escape_string((string)$item->group);

        if (!empty($name) && !empty($phone)) {
            $sql = "INSERT INTO contacts (name, phone, email, contact_group) VALUES ('$name', '$phone', '$email', '$group')";
            if ($conn->query($sql)) {
                $count++;
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Đã nhập thành công {$count} liên hệ từ XML vào CSDL!"
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// ----------------------------------------------------
// 2. GHI DỮ LIỆU TỪ MYSQL RA FILE contacts.xml TRÊN SERVER
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'sync_xml') {
    header('Content-Type: application/json; charset=utf-8');
    syncDatabaseToXML($conn, $xmlFilePath);
    echo json_encode([
        "status" => "success",
        "message" => "Đã ghi dữ liệu thành công vào contacts.xml trên máy chủ!"
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// ----------------------------------------------------
// 3. TẢI FILE contacts.xml VỀ MÁY
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'download_xml') {
    if (!file_exists($xmlFilePath)) {
        syncDatabaseToXML($conn, $xmlFilePath);
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="contacts.xml"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($xmlFilePath));
    readfile($xmlFilePath);
    exit();
}

// Thiết lập định dạng trả về JSON cho các request CRUD
header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

// ----------------------------------------------------
// 4. LẤY DANH SÁCH LIÊN HỆ (GET)
// ----------------------------------------------------
if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ----------------------------------------------------
// 5. THÊM LIÊN HỆ MỚI (POST)
// ----------------------------------------------------
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $conn->real_escape_string($input['name'] ?? '');
    $phone = $conn->real_escape_string($input['phone'] ?? '');
    $email = $conn->real_escape_string($input['email'] ?? '');
    $group = $conn->real_escape_string($input['group'] ?? '');

    $sql = "INSERT INTO contacts (name, phone, email, contact_group) VALUES ('$name', '$phone', '$email', '$group')";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "Đã thêm thành công"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

// ----------------------------------------------------
// 6. CẬP NHẬT LIÊN HỆ (PUT)
// ----------------------------------------------------
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $name = $conn->real_escape_string($input['name'] ?? '');
    $phone = $conn->real_escape_string($input['phone'] ?? '');
    $email = $conn->real_escape_string($input['email'] ?? '');
    $group = $conn->real_escape_string($input['group'] ?? '');

    $sql = "UPDATE contacts SET name='$name', phone='$phone', email='$email', contact_group='$group' WHERE id=$id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "Đã cập nhật thành công"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

// ----------------------------------------------------
// 7. XÓA LIÊN HỆ (DELETE)
// ----------------------------------------------------
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $sql = "DELETE FROM contacts WHERE id=$id";
        if ($conn->query($sql)) {
            echo json_encode(["status" => "success", "message" => "Đã xóa thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    }
    exit();
}