# Smart India Mapping
(Just a random college assignment done for grades. Want more innovative projects ? roam around my GitHub account)

A comprehensive application for mapping and analyzing infrastructure distribution across India, including schools, hospitals, water resources, and power stations. 

## Features

- Interactive map visualization with resource filters
- Admin dashboard with statistics and charts
- Resource management system (add, edit, delete)
- Dark theme design for better visibility
- Mobile responsive layout

## Hosting Instructions

### Option 1: Traditional Web Hosting

1. **Requirements**:
   - PHP 7.4 or higher
   - MySQL database
   - Web server (Apache, Nginx)

2. **Setup**:
   - Upload all files to your web hosting
   - Create a MySQL database using the structure in `database.sql`
   - Update database connection details in `php/config.php`:
     ```php
     $servername = "your-database-server";
     $username = "your-database-username";
     $password = "your-database-password";
     $dbname = "your-database-name";
     ```

3. **Recommended Hosting Providers**:
   - [InfinityFree](https://infinityfree.net/) - Free PHP hosting
   - [000webhost](https://www.000webhost.com/) - Free hosting with PHP and MySQL
   - [Hostinger](https://www.hostinger.com/) - Affordable paid hosting

### Option 2: Local Development Environment

1. **XAMPP/WAMP/MAMP**:
   - Download and install [XAMPP](https://www.apachefriends.org/), [WAMP](https://www.wampserver.com/), or [MAMP](https://www.mamp.info/)
   - Place the project files in the `htdocs` or `www` folder
   - Start Apache and MySQL services
   - Create a database and import the `database.sql` structure
   - Update database connection in `php/config.php`
   - Access the site at `http://localhost/SmartIndiaMapping`

### Option 3: Docker Setup

The application includes Docker configuration for easy deployment:

1. **Prerequisites**:
   - [Docker](https://www.docker.com/get-started)
   - [Docker Compose](https://docs.docker.com/compose/install/)

2. **Launch the application**:
   ```bash
   docker-compose up -d
   ```

3. **Access the application**:
   - Web application: http://localhost:8080
   - phpMyAdmin: http://localhost:8081 (user: smartuser, password: smartpass)

### Option 4: Cloud Hosting

1. **Heroku**:
   - Create a Heroku account
   - Install [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)
   - Create a new Heroku app:
     ```
     heroku create smartindiamapping
     ```
   - Add a MySQL addon:
     ```
     heroku addons:create cleardb:ignite
     ```
   - Get database URL:
     ```
     heroku config | grep CLEARDB_DATABASE_URL
     ```
   - Update `php/config.php` with the database credentials from the URL
   - Deploy to Heroku:
     ```
     git push heroku main
     ```

2. **Railway or Render**:
   - These platforms offer free PHP and MySQL hosting
   - Follow their deployment documentation for PHP applications

## Database Setup

The database structure is defined in `database.sql`. Key tables include:

- `resources_schools` - School data
- `resources_hospitals` - Hospital data
- `resources_water` - Water resource data
- `resources_electricity` - Power station data
- `users` - Admin user accounts
- `activity_log` - User action logs
- `regions` - Region/state information

## Backend API Endpoints

The PHP backend provides these key endpoints:

- `php/get_dashboard_data.php` - Returns summary statistics
- `php/get_resources.php` - Returns resources filtered by type and region
- `php/delete_resource.php` - Deletes a specified resource
- `php/upload.php` - Adds or updates resource data
- `php/login.php` - Handles user authentication
- `php/logout.php` - Handles user logout

## Frontend Configuration

The application's frontend is built with HTML, CSS (via TailwindCSS), and JavaScript:

- `index.html` - Landing page
- `map.html` - Interactive map interface
- `admin.html` - Admin dashboard

## Security Considerations

1. Ensure your web hosting provider supports HTTPS
2. Keep PHP and MySQL updated to the latest versions
3. Consider implementing a .htaccess file for additional security
4. Set appropriate file permissions (typically 755 for directories, 644 for files)

## Troubleshooting

1. **Database Connection Issues**:
   - Verify database credentials in `php/config.php`
   - Check if MySQL service is running
   - Ensure your hosting supports the MySQL version you're using

2. **File Permissions**:
   - Make sure PHP has write access to directories where uploads occur
   - Check error logs for permission-related issues

3. **API Not Working**:
   - Enable error reporting temporarily to identify issues:
     ```php
     ini_set('display_errors', 1);
     error_reporting(E_ALL);
     ```
   - Check browser console for AJAX errors
   - Verify PHP version compatibility

For additional support, please open an issue on the GitHub repository.

## License

This project is available for open use.
