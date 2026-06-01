CREATE TABLE IF NOT EXISTS users (
    id CHAR(32) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE IF NOT EXISTS conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(32) NOT NULL,
    input_format VARCHAR(10) NOT NULL,
    output_format VARCHAR(10) NOT NULL,
    input_content TEXT NOT NULL,
    output_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS conversion_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversion_id INT NOT NULL,
    user_id CHAR(32) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversion_id) REFERENCES conversions(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(32) NOT NULL UNIQUE,
    auto_save TINYINT(1) DEFAULT 1,
    default_input_format VARCHAR(10) DEFAULT 'json',
    default_output_format VARCHAR(10) DEFAULT 'yaml',
    default_transformation VARCHAR(20) DEFAULT 'none',
    default_indentation INT DEFAULT 2,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS value_mappings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(32) NOT NULL,
    from_key VARCHAR(100) NOT NULL,
    to_key VARCHAR(100) NOT NULL,
    from_value VARCHAR(100),
    to_value VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
