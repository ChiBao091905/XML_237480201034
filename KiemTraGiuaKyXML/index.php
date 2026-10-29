<?php
$conn = new mysqli("localhost", "root", "", "xml_contacts");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
$xmlFilePath = __DIR__ . "/contacts.xml";
function syncDatabaseToXML($conn, $filePath) {
    $result = $conn->query("SELECT * FROM contacts ORDER BY id ASC");
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;
    $root = $xml->createElement("contacts");
    $xml->appendChild($root);
    while ($row = $result->fetch_assoc()) {
        $node = $xml->createElement("contact");
        $node->setAttribute("id", $row['id']);
        $node->appendChild($xml->createElement("name", htmlspecialchars($row['name'])));
        $node->appendChild($xml->createElement("phone", htmlspecialchars($row['phone'])));
        $node->appendChild($xml->createElement("email", htmlspecialchars($row['email'])));
        $node->appendChild($xml->createElement("group", htmlspecialchars(mb_convert_case(trim($row['contact_group']), MB_CASE_TITLE, "UTF-8"))));
        $root->appendChild($node);
    }
    $xml->save($filePath);
}
if (isset($_GET['action']) && $_GET['action'] === 'download_xml') {
    if (!file_exists($xmlFilePath)) syncDatabaseToXML($conn, $xmlFilePath);
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="contacts.xml"');
    readfile($xmlFilePath);
    exit();
}
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['api'];
    if ($action === 'import_xml' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["status" => "error", "message" => "File không hợp lệ."]);
            exit();
        }
        $xml = simplexml_load_file($_FILES['xml_file']['tmp_name']);
        if ($xml === false) {
            echo json_encode(["status" => "error", "message" => "Cấu trúc XML không đúng."]);
            exit();
        }
        $count = 0;
        foreach ($xml->contact as $item) {
            $name = $conn->real_escape_string(trim((string)$item->name));
            $phone = $conn->real_escape_string(trim((string)$item->phone));
            $email = $conn->real_escape_string(trim((string)$item->email));
            $group = $conn->real_escape_string(mb_convert_case(trim((string)$item->group), MB_CASE_TITLE, "UTF-8"));
            if (!empty($name) && !empty($phone)) {
                $check = $conn->query("SELECT id FROM contacts WHERE phone='$phone'");
                if ($check->num_rows == 0) {
                    if ($conn->query("INSERT INTO contacts (name, phone, email, contact_group) VALUES ('$name', '$phone', '$email', '$group')")) $count++;
                }
            }
        }
        echo json_encode(["status" => "success", "message" => "Đã nhập thành công {$count} liên hệ!"]);
        exit();
    }
    if ($action === 'sync_xml') {
        syncDatabaseToXML($conn, $xmlFilePath);
        echo json_encode(["status" => "success", "message" => "Đã ghi dữ liệu vào contacts.xml trên server!"]);
        exit();
    }
    if ($action === 'list') {
        $result = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['contact_group'] = mb_convert_case(trim($row['contact_group']), MB_CASE_TITLE, "UTF-8");
            $data[] = $row;
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string(trim($input['name'] ?? ''));
        $phone = $conn->real_escape_string(trim($input['phone'] ?? ''));
        $email = $conn->real_escape_string(trim($input['email'] ?? ''));
        $group = $conn->real_escape_string(mb_convert_case(trim($input['group'] ?? ''), MB_CASE_TITLE, "UTF-8"));
        $sql = "INSERT INTO contacts (name, phone, email, contact_group) VALUES ('$name', '$phone', '$email', '$group')";
        echo json_encode($conn->query($sql) ? ["status" => "success"] : ["status" => "error", "message" => $conn->error]);
        exit();
    }
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $name = $conn->real_escape_string(trim($input['name'] ?? ''));
        $phone = $conn->real_escape_string(trim($input['phone'] ?? ''));
        $email = $conn->real_escape_string(trim($input['email'] ?? ''));
        $group = $conn->real_escape_string(mb_convert_case(trim($input['group'] ?? ''), MB_CASE_TITLE, "UTF-8"));
        $sql = "UPDATE contacts SET name='$name', phone='$phone', email='$email', contact_group='$group' WHERE id=$id";
        echo json_encode($conn->query($sql) ? ["status" => "success"] : ["status" => "error", "message" => $conn->error]);
        exit();
    }
    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        echo json_encode($conn->query("DELETE FROM contacts WHERE id=$id") ? ["status" => "success"] : ["status" => "error", "message" => $conn->error]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>DANH BẠ CÁ NHÂN</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px auto; background: #f4f4f9; max-width: 760px; }
        .section { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 100px; font-weight: bold; }
        .form-group input, .form-group select { padding: 6px 10px; font-size: 0.95em; border-radius: 4px; border: 1px solid #ccc; width: 240px; }
        button { padding: 8px 15px; font-size: 0.95em; border-radius: 4px; border: none; cursor: pointer; font-weight: bold; }
        .btn-add { background: #2ecc71; color: white; margin-top: 5px; }
        .btn-update { background: #3498db; color: white; margin-top: 5px; display: inline-block; }
        .btn-cancel { background: #95a5a6; color: white; margin-top: 5px; display: none; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .filter-box, .btn-group-actions { display: flex; align-items: center; gap: 8px; }
        .btn-download { background: #e67e22; color: white; padding: 7px 12px; font-size: 0.88em; }
        .btn-save-xml { background: #8e44ad; color: white; padding: 7px 12px; font-size: 0.88em; }
        .btn-import-xml { background: #16a085; color: white; padding: 7px 12px; font-size: 0.88em; }
        .card { background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; }
        .card-actions { position: absolute; top: 15px; right: 15px; }
        .card-actions button { padding: 4px 8px; font-size: 0.8em; margin-left: 5px; }
        .btn-edit-item { background: #f39c12; color: white; }
        .btn-delete-item { background: #e74c3c; color: white; }
        .name { font-weight: bold; color: #2c3e50; font-size: 1.1em; margin-bottom: 5px; }
        .badge { background: #3498db; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; }
        .empty { color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>
    <h2>DANH BẠ CÁ NHÂN</h2>
    <div class="section">
        <h3 id="form-title">➕ Thêm liên hệ mới</h3>
        <form id="add-contact-form" onsubmit="handleSubmit(event)">
            <input type="hidden" id="input-id">
            <div class="form-group"><label>Họ và Tên:</label><input type="text" id="input-name" required placeholder="Nhập tên..."></div>
            <div class="form-group"><label>Số điện thoại:</label><input type="tel" id="input-phone" required placeholder="Nhập SĐT..."></div>
            <div class="form-group"><label>Email:</label><input type="email" id="input-email" required placeholder="Nhập email..."></div>
            <div class="form-group"><label>Nhóm:</label><input type="text" id="input-group" required placeholder="Công việc, Gia đình..."></div>
            <button type="submit" id="btn-submit" class="btn-add">Thêm vào Danh bạ</button>
            <button type="button" id="btn-cancel" class="btn-cancel" onclick="resetForm()">Hủy</button>
        </form>
    </div>
    <div class="section toolbar">
        <div class="filter-box">
            <label for="group-select"><strong>Lọc nhóm:</strong></label>
            <select id="group-select" onchange="filterContacts()">
                <option value="ALL">Tất cả nhóm</option>
            </select>
        </div>
        <div class="btn-group-actions">
            <input type="file" id="xml-file-input" accept=".xml" style="display: none;" onchange="handleFileUpload(event)">
            <button type="button" class="btn-import-xml" onclick="document.getElementById('xml-file-input').click()">📤 Nhập từ XML</button>
            <button type="button" class="btn-save-xml" onclick="exportToXML()">💾 Ghi dữ liệu tạm thời vào CSDL</button>
            <button type="button" class="btn-download" onclick="downloadXML()">📥 Tải XML</button>
        </div>
    </div>
    <div id="contact-list">Đang tải dữ liệu...</div>
    <script>
        let contactsData = [];
        function toTitleCase(str) {
            return (str || '').toLowerCase().trim().split(/\s+/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        }
        async function fetchContacts() {
            try {
                let response = await fetch('index.php?api=list');
                contactsData = await response.json();
                updateGroupFilter();
                filterContacts();
            } catch (error) {
                document.getElementById("contact-list").innerHTML = `<p class="empty">Không thể tải dữ liệu.</p>`;
            }
        }
        function updateGroupFilter() {
            const select = document.getElementById("group-select");
            const currentSelected = select.value.toLowerCase().trim();
            select.innerHTML = '<option value="ALL">Tất cả nhóm</option>';
            const groupMap = new Map();
            contactsData.forEach(c => {
                const formattedGrp = toTitleCase(c.contact_group);
                const key = formattedGrp.toLowerCase();
                if (formattedGrp && !groupMap.has(key)) {
                    groupMap.set(key, formattedGrp);
                }
            });
            groupMap.forEach((formattedName, key) => {
                const option = document.createElement("option");
                option.value = key;
                option.textContent = formattedName;
                if (currentSelected === key) option.selected = true;
                select.appendChild(option);
            });
        }
        function filterContacts() {
            const selectedGroup = document.getElementById("group-select").value.toLowerCase().trim();
            let htmlOutput = "";
            contactsData.forEach(contact => {
                const displayGroup = toTitleCase(contact.contact_group);
                const contactGrpKey = displayGroup.toLowerCase();
                if (selectedGroup === "all" || contactGrpKey === selectedGroup) {
                    htmlOutput += `
                        <div class="card">
                            <div class="name">${contact.name} <span class="badge">${displayGroup}</span></div>
                            <div>📞 SĐT: ${contact.phone}</div>
                            <div>✉️ Email: ${contact.email}</div>
                            <div class="card-actions">
                                <button class="btn-edit-item" onclick="editContact(${contact.id})">Sửa</button>
                                <button class="btn-delete-item" onclick="deleteContact(${contact.id})">Xóa</button>
                            </div>
                        </div>`;
                }
            });
            document.getElementById("contact-list").innerHTML = htmlOutput || `<p class="empty">Không có liên hệ nào trong nhóm này.</p>`;
        }
        async function handleSubmit(event) {
            event.preventDefault();
            const id = document.getElementById("input-id").value;
            const contactData = {
                name: document.getElementById("input-name").value.trim(),
                phone: document.getElementById("input-phone").value.trim(),
                email: document.getElementById("input-email").value.trim(),
                group: toTitleCase(document.getElementById("input-group").value)
            };
            const endpoint = id ? 'index.php?api=update' : 'index.php?api=add';
            if (id) contactData.id = id;
            await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(contactData)
            });
            alert(id ? "Cập nhật thành công!" : "Thêm mới thành công!");
            resetForm();
            fetchContacts();
        }
        function editContact(id) {
            const contact = contactsData.find(c => c.id == id);
            if (!contact) return;
            document.getElementById("input-id").value = contact.id;
            document.getElementById("input-name").value = contact.name;
            document.getElementById("input-phone").value = contact.phone;
            document.getElementById("input-email").value = contact.email;
            document.getElementById("input-group").value = toTitleCase(contact.contact_group);
            document.getElementById("form-title").textContent = "✏️ Cập nhật liên hệ";
            let btnSubmit = document.getElementById("btn-submit");
            btnSubmit.textContent = "Lưu thay đổi";
            btnSubmit.className = "btn-update";
            document.getElementById("btn-cancel").style.display = "inline-block";
        }
        async function deleteContact(id) {
            if (confirm("Xóa liên hệ này?")) {
                await fetch(`index.php?api=delete&id=${id}`);
                fetchContacts();
            }
        }
        function resetForm() {
            document.getElementById("add-contact-form").reset();
            document.getElementById("input-id").value = "";
            document.getElementById("form-title").textContent = "➕ Thêm liên hệ mới";
            let btnSubmit = document.getElementById("btn-submit");
            btnSubmit.textContent = "Thêm vào Danh bạ";
            btnSubmit.className = "btn-add";
            document.getElementById("btn-cancel").style.display = "none";
        }
        async function exportToXML() {
            try {
                let res = await fetch('index.php?api=sync_xml');
                let result = await res.json();
                alert(result.message);
            } catch (e) { alert("Lỗi khi ghi file!"); }
        }
        function downloadXML() {
            window.location.href = 'index.php?action=download_xml';
        }
        async function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('xml_file', file);
            try {
                let res = await fetch('index.php?api=import_xml', { method: 'POST', body: formData });
                let result = await res.json();
                alert(result.message);
                fetchContacts();
            } catch (e) { alert("Lỗi khi import XML!"); }
            event.target.value = '';
        }
        fetchContacts();
    </script>
</body>
</html>