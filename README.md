# JSON Converter

A web platform for converting between JSON, YAML, XML, CSV, .properties and .ini formats.
Built with PHP 8.3, MySQL 8, HTML/CSS/JS as a university Web Technologies project.

## Features

- User registration and login with session management
- Convert between JSON, YAML, XML, CSV, .properties, .ini
- Pretty-print or minify output
- Key transformations: camelCase, PascalCase, snake_case, kebab-case, UPPER_CASE
- Value mappings (e.g. `ver` → `version`, `1.0` → `latest`)
- Conversion history with comments
- Per-user settings (default formats, transformation, indentation, auto-save toggle)
- CSRF protection on all forms

## Quick Start with Docker (recommended)

Requirements: Docker Desktop with WSL 2

```bash
git clone <repo-url>
cd json_converter
docker-compose up --build
```

App available at: `http://localhost:8080/json_converter`

The database is created automatically on first run. Tables are created from `sql/setup.sql`
and sample data is loaded from `sql/seed.sql`.

### Test accounts

| Username | Password | Notes             |
|----------|----------|-------------------|
| test     | test     | Regular user      |
| test1    | test1    | Second test user  |
| admin    | admin    | "Admin account"   |

### Stop and clean up

```bash
docker-compose down          # stop containers, keep database volume
docker-compose down -v       # stop containers and delete database
```

### Reset to seed data

```bash
docker-compose down -v
docker-compose up
```

## Local Setup (XAMPP)

Requirements: XAMPP with PHP 8.3 and MySQL 8

1. Start Apache and MySQL in the XAMPP Control Panel
2. Clone this repo into `C:\xampp\htdocs\json_converter`
3. Open phpMyAdmin (`http://localhost/phpmyadmin`)
4. Create a database called `json_converter` with collation `utf8_general_ci`
5. Import `sql/setup.sql`
6. Import `sql/seed.sql`
7. Open `http://localhost/json_converter`

No configuration needed - `config.php` uses XAMPP defaults automatically.

## Project Structure

```
├── index.php              # Converter controller
├── history.php            # History controller
├── settings.php           # Settings controller
├── register.php           # Registration page
├── login.php              # Login page
├── logout.php             # Logout
├── add_comment.php        # Add comment action
├── delete_comment.php     # Delete comment action
├── auth_guard.php         # Session + CSRF functions
├── config.php             # Configuration (reads .env or uses defaults)
├── db.php                 # Database connection
├── convert.php            # Format parsing and output functions
├── transformations.php    # Key transformation and value mapping functions
├── yaml_json.php          # Custom YAML parser/serializer
├── views/
│   ├── converter.php      # Converter HTML view
│   ├── history.php        # History HTML view
│   └── settings.php       # Settings HTML view
├── includes/
│   ├── header.php         # Shared page header and nav
│   └── footer.php         # Shared page footer
├── public/
│   └── style.css          # Stylesheet
├── sql/
│   ├── setup.sql          # Database schema
│   └── seed.sql           # Sample data
├── docker-compose.yml
├── Dockerfile
└── .env.example           # Environment variable template
```

## Configuration

For Docker or custom environments, copy `.env.example` to `.env` and adjust:

```
DB_HOST=db
DB_USER=jsonconv
DB_PASS=jsonconv123
DB_NAME=json_converter
APP_URL=http://localhost:8080/json_converter
```

XAMPP users do not need a `.env` file - the defaults in `config.php` work out of the box.
