-- Clear existing data (child tables first to respect foreign keys)
DELETE FROM conversion_comments;
DELETE FROM conversions;
DELETE FROM value_mappings;
DELETE FROM settings;
DELETE FROM users;

-- Passwords: test = 'test', test1 = 'test1', admin = 'admin'
INSERT INTO users (id, username, email, password) VALUES
    ('f404b93e4c1d4fbf543929ffe3eb096d', 'test',  'test@test.com',   '$2y$10$NJBK0eG6RZN6U/Of/AgW8ub6VXfHMUK7Zc3YrxwDucj5BI/S2uSHm'),
    ('039ce5650638f14a89cdb855866e8dc2', 'test1', 'test1@test1.com', '$2y$10$rjiTZ8og8oLENRoJMd6x4ONWAg/p2otQFbSLv6tZThbU1e0uNMAIq'),
    ('a461e14748b47049c2b421652dbf9e49', 'admin', 'admin@admin.com', '$2y$10$ORAGyuF33SgvRlQ17XHymuDU86zOwjVCjnDNgjj/yhyOd1czSBPpy');

INSERT INTO settings (user_id, auto_save, default_input_format, default_output_format, default_transformation, default_indentation) VALUES
    ('f404b93e4c1d4fbf543929ffe3eb096d', 1, 'json', 'yaml', 'none',       2),
    ('039ce5650638f14a89cdb855866e8dc2', 1, 'yaml', 'json', 'snake_case', 4),
    ('a461e14748b47049c2b421652dbf9e49', 0, 'json', 'json', 'none',       2);

INSERT INTO value_mappings (user_id, from_key, to_key, from_value, to_value) VALUES
    ('f404b93e4c1d4fbf543929ffe3eb096d', 'ver',     'version', '1.0',   'latest'),
    ('f404b93e4c1d4fbf543929ffe3eb096d', 'env',     'environment', NULL, NULL),
    ('039ce5650638f14a89cdb855866e8dc2', 'enabled', 'active',  'true',  '1');

INSERT INTO conversions (user_id, input_format, output_format, input_content, output_content) VALUES
    (
        'f404b93e4c1d4fbf543929ffe3eb096d',
        'json', 'yaml',
        '{"name": "json-converter", "ver": "1.0", "env": "production"}',
        'name: json-converter\nversion: latest\nenvironment: production'
    ),
    (
        'f404b93e4c1d4fbf543929ffe3eb096d',
        'json', 'xml',
        '{"user": {"id": 1, "username": "test", "active": true}}',
        '<?xml version="1.0"?><root><user><id>1</id><username>test</username><active>1</active></user></root>'
    ),
    (
        '039ce5650638f14a89cdb855866e8dc2',
        'yaml', 'json',
        "name: example\nversion: 2.0\ndebug: false",
        '{"name": "example", "version": "2.0", "debug": false}'
    ),
    (
        'a461e14748b47049c2b421652dbf9e49',
        'json', 'csv',
        '[{"id": 1, "name": "Alice"}, {"id": 2, "name": "Bob"}]',
        "id,name\n1,Alice\n2,Bob"
    );

INSERT INTO conversion_comments (conversion_id, user_id, comment) VALUES
    (1, 'f404b93e4c1d4fbf543929ffe3eb096d', 'Used ver->version mapping, works great'),
    (2, 'f404b93e4c1d4fbf543929ffe3eb096d', 'Testing JSON to XML'),
    (3, '039ce5650638f14a89cdb855866e8dc2', 'snake_case applied automatically from settings');
