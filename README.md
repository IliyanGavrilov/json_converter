# JSON Converter

A web app for converting between JSON, YAML, XML, CSV and .properties/.ini formats.
Built with plain PHP, MySQL, HTML, CSS and JS as a university project.

## Features
- User registration and login
- Convert between JSON, YAML, XML, CSV, .properties/.ini
- Conversion history with comments
- Text transformations (camelCase, snake_case, UPPER_CASE)
- Value mapping (e.g. `ver` → `version`)
- Per-user settings

## Local Setup (XAMPP)

1. Install XAMPP from https://www.apachefriends.org — use PHP 8.2
2. Start Apache and MySQL in the XAMPP Control Panel
3. Clone this repo into `C:\xampp\htdocs\json_converter`
4. Copy `config.example.php` to `config.php` and adjust if needed (default values work out of the box with XAMPP)
5. Open `http://localhost/phpmyadmin`, create a database called `json_converter` with collation `utf8_general_ci`
6. Import `setup.sql` from the repo root into that database
7. Go to `http://localhost/json_converter`
