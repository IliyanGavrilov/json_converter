CREATE DATABASE IF NOT EXISTS json_converter;
USE json_converter;

CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    input_format VARCHAR(10) NOT NULL,
    output_format VARCHAR(10) NOT NULL,
    input_content TEXT NOT NULL,
    output_content TEXT NOT NULL,
    comment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL UNIQUE,
    auto_save TINYINT(1) DEFAULT 1,
    default_input_format VARCHAR(10) DEFAULT 'json',
    default_output_format VARCHAR(10) DEFAULT 'yaml',
    default_transformation VARCHAR(20) DEFAULT 'none',
    default_indentation INT DEFAULT 2,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE value_mappings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    from_key VARCHAR(100) NOT NULL,
    to_key VARCHAR(100) NOT NULL,
    from_value VARCHAR(100),
    to_value VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);