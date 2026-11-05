# FlatMate

## Project Name
**FlatMate – Shared Apartment Management System**

## Description
FlatMate is a web application designed to simplify the management of shared apartments. The system allows users living together to organize apartment-related information and manage shared expenses.

The application aims to improve communication and transparency between flatmates by providing a common platform for managing household finances.

## Technologies
The project uses the following technologies:

- **PHP 8.2+** – server-side programming language.
- **Laravel 12** – PHP web application framework.
- **MySQL** – relational database management system.
- **Blade** – Laravel templating engine.
- **HTML5** – page structure.
- **CSS3** – application styling.
- **JavaScript** – client-side functionality.
- **Composer** – PHP dependency management.
- **Git and GitHub** – version control and project collaboration.

## Requirements
To run the application locally, the following software is required:

- PHP 8.2 or newer.
- Composer.
- MySQL database.
- Node.js and npm.
- Git.
- A local development environment, such as Laragon or XAMPP.

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/dimkavey/FlatMate.git
   ```

2. Navigate to the project directory:

   ```bash
   cd FlatMate
   ```

3. Install PHP dependencies:

   ```bash
   composer install
   ```

4. Create the environment configuration file:

   ```bash
   cp .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Create a MySQL database named `flatmate`.

7. Configure the database connection in the `.env` file:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=flatmate
   DB_USERNAME=root
   DB_PASSWORD=
   ```

8. Run database migrations:

   ```bash
   php artisan migrate
   ```

9. Install frontend dependencies:

   ```bash
   npm install
   ```

10. Build frontend assets:

    ```bash
    npm run build
    ```

## Running

To start the Laravel development server, execute:

```bash
php artisan serve
```

The application will be available at:

**http://127.0.0.1:8000**

For frontend development with Vite, run the following command in a separate terminal:

```bash
npm run dev
```

## Authors

- **Oleksandr Kuzin**
- **Dmytrii Yakovyshyn**
