# Finance Tracker PHP Backend

This repository contains the PHP backend code for the [Finance Tracker Android App](https://github.com/IcbtFinalAssignment/Finance-Tracker). It includes the necessary API endpoints and database management for tracking transactions, user authentication, and report generation.

## Table of Contents

- [Features](#features)
- [File Structure](#file-structure)
- [API Endpoints](#api-endpoints)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Features

- User Authentication (Registration, Login, Password Reset)
- Transaction Management (Add, Edit, Delete, Fetch)
- Secure API requests using access keys and API keys
- PDF Report Generation
- Budget Management
- Monthly and Date Range Filtering
- Email-based OTP for password recovery

## File Structure

- **.env**: Contains environment variables for database connection and API keys.
- **add_record.php**: API endpoint for adding a new transaction (income/expense).
- **budget.php**: Handles budget-related operations such as budget setup and tracking.
- **db.sql**: SQL file to initialize the MySQL database and required tables.
- **env_loader.php**: Loads environment variables from the `.env` file.
- **functions.php**: Contains reusable functions for database operations, validations, etc.
- **get_record.php**: API endpoint for fetching transactions based on date range or month.
- **login.php**: API endpoint for user authentication (login).
- **password_reset.php**: Handles password reset operations such as sending OTP, validating OTP, and updating passwords.
- **register.php**: API endpoint for user registration.
- **security_headers.php**: Adds security headers to API responses for enhanced security.
- **mailer/**: Contains scripts for sending emails (e.g., OTPs for password reset).

## API Endpoints

### Authentication

- **POST** `/register.php` - Register a new user.
- **POST** `/login.php` - User login.
- **POST** `/password_reset.php` - Handle password reset requests (sending OTP, validating OTP, updating password).

### Transaction Management

- **POST** `/add_record.php` - Add a new income/expense record.
- **GET** `/get_record.php` - Fetch transactions by email, API key, and date range.

### Budget Management

- **POST** `/budget.php` - Set and manage user budgets.

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/IcbtFinalAssignment/Finance-Tracker-Backend.git
   ```

2. Ensure your server environment has PHP 7.4 or higher and MySQL.

3. Install required PHP extensions: 
   - `mysqli`
   - `mbstring`
   - `json`

## Configuration

1. Copy the `.env.example` file to `.env` and update the environment variables:
   ```bash
   cp .env.example .env
   ```

2. Set the following configurations in `.env`:
   ```bash

   ACCESS_KEY=your_access_key
   ```

## Database Setup

1. Create a MySQL database.
2. Import the provided `db.sql` file to set up the necessary tables:
   ```bash
   mysql -u your_username -p your_database < db.sql
   ```

## Usage

- Use tools like [Postman](https://www.postman.com/) to test the API endpoints.
- To integrate this PHP backend with the Android app, point the app’s API URL to the server where the backend is hosted.

## Contributing

If you wish to contribute, please fork this repository, create a new branch, and submit a pull request with your improvements or bug fixes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
