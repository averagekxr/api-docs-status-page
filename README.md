# API Documentation & Status Page

## Overview

Welcome to the **API Documentation & Status Page** repository! This project provides a dynamic and user-friendly API documentation interface, along with a real-time status page for monitoring service health and performance. You can easily track the status of your services and view comprehensive API documentation for seamless integration.

**Key Features:**
- **API Documentation Page**: Clean and easy-to-read API documentation with endpoint details and examples.
- **Real-Time Service Status Page**: Monitors and displays the operational status of services in real time (Operational, Degraded, or Outage).
- **SQL Database Integration**: Stores status data and allows easy management of service information.
- **Fully Customizable**: The project is designed to be easily extendable and adapted for your needs.

## Project Setup

### Prerequisites

To set up this project, you will need:
- **PHP 8.0+**
- **MySQL/MariaDB**
- **Apache or Nginx Server**

### Installation Steps

1. **Clone the repository**:

```bash
git clone https://github.com/karldc/api-docs-status-page.git
cd api-docs-status-page
```

2. **Import the Database Schema**:

The project requires a MySQL/MariaDB database to store service status data. Import the provided SQL dump into your database.

```bash
mysql -u your-username -p < path-to-sql-file.sql
```

Alternatively, you can use **phpMyAdmin** or any database management tool to import the SQL file.

3. **Database Configuration**:

Edit the database connection settings in the PHP files to match your database credentials.

```php
// Example: database.php
$host = 'localhost';
$db = 'apistat';
$user = 'your-username';
$pass = 'your-password';
```

4. **Upload to Your Server**:

Once the files are uploaded to your server, navigate to the API documentation and status pages in your browser to begin using the system.

---

## SQL Structure

The database schema consists of two main tables for tracking service data and their operational statuses.

### `services` Table

Stores details about the services being monitored.

```sql
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

### `status_checks` Table

Records the status and response time for each service at the time of check.

```sql
CREATE TABLE `status_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `status` enum('operational','degraded','outage') NOT NULL,
  `response_time` decimal(8,2) NOT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `status_checks_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
);
```

### Example Data

#### `services` Table

```sql
INSERT INTO `services` (`name`, `description`, `created_at`) VALUES
('Service 1', 'Main service of the platform', NOW()),
('Service 2', 'Secondary service for backups', NOW());
```

#### `status_checks` Table

```sql
INSERT INTO `status_checks` (`service_id`, `status`, `response_time`, `checked_at`) VALUES
(1, 'operational', 0.15, NOW()),
(2, 'outage', 1.34, NOW());
```

---

## API Documentation

### Authentication

To use the API, you'll need a valid token. This is the format for the authentication request:

```http
GET /api/tokencheck.php?token=YOUR_TOKEN
```

#### Parameters:

| Parameter | Required | Description                 |
|-----------|----------|-----------------------------|
| `token`   | Yes      | Your unique API access token. |

#### Example Response:

```json
{
  "status": "success",
  "code": 200,
  "data": {
    "authentication": {
      "valid": true,
      "token": "valid_token_string"
    },
    "user": { /* user data */ }
  }
}
```

### API Endpoints

1. **Get Service Status**

   **Endpoint**: `/api/statuscheck.php?service_id=ID`

   Retrieve the current status of a specific service.

   Example Response:

   ```json
   {
     "service_id": 1,
     "status": "operational",
     "response_time": 0.15,
     "checked_at": "2025-02-03T23:57:05Z"
   }
   ```

2. **Get All Services**

   **Endpoint**: `/api/services.php`

   Retrieve a list of all monitored services.

   Example Response:

   ```json
   [
     {
       "id": 1,
       "name": "Service 1",
       "description": "Main service of the platform",
       "created_at": "2025-02-03T23:57:05Z"
     },
     {
       "id": 2,
       "name": "Service 2",
       "description": "Secondary service for backups",
       "created_at": "2025-02-03T23:57:05Z"
     }
   ]
   ```

---

## Contributions

Contributions are welcome! If you would like to contribute to this project, feel free to fork the repository, make your changes, and submit a pull request. If you encounter any issues or have suggestions, please open an issue in the GitHub repository.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.

---

## Acknowledgments

- **PHP** and **MySQL/MariaDB** for server-side and database management.
- Open-source libraries used in this project.
- **Icon libraries** such as FontAwesome for icons.

---

## Contact

For any questions or additional support, feel free to open an issue or reach out to us at [asiridoz@proton.me].
```
