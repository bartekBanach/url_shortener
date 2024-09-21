# Symfony URL Shortener Project

## Description

This Symfony-based URL Shortener application includes the following functionalities:

- **Guest Users**: Can shorten up to 10 URLs per day.
- **Logged Users**: Can shorten an unlimited number of URLs.
- **URL Shortening**: Converts URLs into 7-character base-62 encoded codes.
- **Tagging URLs**: Users can tag their shortened URLs and filter URLs list by tags.
- **Admin Panel**: Includes functionality for managing URLs, tags, and users.

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. Install the dependencies:
   ```bash
   composer install
   ```

3. Set up your environment configuration:
   - Create a `.env.local` file to override default values:
     ```bash
     cp .env .env.local
     ```
   - Update the database connection string in the `.env.local` file:
     ```
     DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name"
     ```
   - Set the Mailer DSN:
     ```
     MAILER_DSN=smtp://username:password@smtp.example.com:587?encryption=tls&auth_mode=login
     ```

   - For testing, update the database connection string in the `.env.test` file:
     ```
     DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name"
     ```

4. Load the database schema:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Load data fixtures:
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. To set up the test database and load test fixtures:
   ```bash
   php bin/console doctrine:migrations:migrate --env=test
   php bin/console doctrine:fixtures:load --env=test
   ```

## Running Tests

Run the test suite with PHPUnit:
```bash
./bin/phpunit
```
