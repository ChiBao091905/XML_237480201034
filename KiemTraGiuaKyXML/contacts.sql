CREATE DATABASE IF NOT EXISTS xml_contacts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xml_contacts;

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    contact_group VARCHAR(100) NOT NULL
);

-- Thêm vài dữ liệu mẫu
INSERT INTO contacts (name, phone, email, contact_group) VALUES 
('Nguyễn Văn A', '0901234567', 'nva@gmail.com', 'Công việc'),
('Trần Thị B', '0987654321', 'ttb@gmail.com', 'Gia đình');