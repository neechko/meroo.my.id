# meroo.my.id

<p align="center">
  <a href="https://meroo.my.id/">
    <strong>Visit meroo.my.id →</strong>
  </a>
</p>

<p align="center">
  Personal website built to showcase my profile, projects, social links, and other information.
</p>

---

## Website

**Live Website:**
https://meroo.my.id/

---

## Features

* Personal profile
* Social media links
* Dynamic content management
* MySQL database integration
* Responsive design
* Lightweight and simple interface
* Database-driven configuration

---

## Tech Stack

* **PHP**
* **MySQL**
* **HTML5**
* **CSS3**
* **JavaScript**
* **Apache**
* **phpMyAdmin**

---

## Project Structure

```text
meroo.my.id/
├── config.php
├── index.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
├── admin/
└── ...
```

> Project structure may change as development continues.

---

## Database

This project uses **MySQL** for storing dynamic website data.

For local development with XAMPP:

1. Start **Apache** and **MySQL** from XAMPP.
2. Open phpMyAdmin.
3. Create a database.
4. Import the provided SQL database.
5. Configure the database credentials in `config.php`.

Example:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## Installation

Clone the repository:

```bash
git clone https://github.com/neechko/meroo.my.id.git
```

Enter the project directory:

```bash
cd meroo.my.id
```

Configure the database in:

```text
config.php
```

Then import the database through phpMyAdmin.

For XAMPP, place the project inside:

```text
C:\xampp\htdocs\
```

Then open:

```text
http://localhost/meroo.my.id/
```

---

## Configuration

Before running the project, make sure the following configuration is correct:

```php
DB_HOST
DB_NAME
DB_USER
DB_PASS
```

For production hosting, use the database credentials provided by your hosting provider.

---

## Status

**Currently under development**

The project is actively being updated and restructured.

---

## Links

* Website — https://meroo.my.id/
* GitHub — https://github.com/neechko/meroo.my.id

---

## License

This project is for personal use and development purposes.

© 2026 **meroo.my.id**
