# Smart India Mapping

A web application for visualizing and managing resource data across states and districts in India. The application displays an interactive map that shows schools, hospitals, water resources, and electricity infrastructure.

## Features

- Interactive map of India using Leaflet.js
- Resource data visualization for schools, hospitals, water resources, and electricity
- Filter resources by type and region
- Data analytics with Chart.js
- Admin panel for managing data
- CSV upload functionality for bulk data import
- Modern UI with Tailwind CSS

## Tech Stack

- **Frontend:** HTML, JavaScript, Tailwind CSS, Leaflet.js, Chart.js
- **Backend:** PHP, MySQL

## Installation

### Prerequisites

- Web server with PHP 7.4+ support (Apache, Nginx, etc.)
- MySQL 5.7+ or MariaDB 10.3+
- PHP with PDO and MySQLi extensions enabled

### Setup Instructions

1. Clone or download this repository to your web server's document root.

2. Create a MySQL database and set up the database schema:
   - Use the SQL script in `database/setup.sql` to create the database and tables.
   - You can do this via phpMyAdmin or MySQL command line:
     ```
     mysql -u yourusername -p < database/setup.sql
     ```

3. Configure the database connection:
   - Edit `php/config.php` and update the database connection details:
     ```php
     $servername = "localhost";
     $username = "your_mysql_username";
     $password = "your_mysql_password";
     $dbname = "smart_india_mapping";
     ```

4. Ensure the web server has write permissions to the application directory for file uploads.

5. Access the application through your web browser.

### GeoJSON Map Data

The application requires a GeoJSON file of India's states for the interactive map. You should:

1. Create a directory named `assets` in the root directory if it doesn't already exist.
2. Find a suitable GeoJSON file for India's states and save it as `assets/india-states.geojson`.

You can find GeoJSON files for India from sources like:
- [DataMeet](https://github.com/datameet/maps/tree/master/States)
- [Natural Earth Data](https://www.naturalearthdata.com/downloads/)

## Usage

### Viewing the Map

1. Navigate to the main page or click on "Map" in the navigation.
2. Use the filter options on the left to show specific resource types.
3. Click on any state to view detailed information about that region.
4. Hover over markers to see brief information, and click for detailed popups.

### Admin Panel

1. Access the admin panel by clicking "Admin" in the navigation.
2. Log in with the default credentials:
   - Username: admin
   - Password: admin123
   - (Be sure to change this password after first login in a production environment)

3. From the admin panel, you can:
   - View dashboard statistics
   - Upload CSV files with resource data
   - Manage existing resources
   - View activity logs

### CSV Upload Format

When uploading data, ensure your CSV files follow these formats:

#### Schools CSV Format
Required fields: name, state, latitude, longitude, level (primary, secondary, higher, university)
Optional fields: district, students, teachers, established_year, facilities

#### Hospitals CSV Format
Required fields: name, state, latitude, longitude, hospital_type (government, private, charity)
Optional fields: district, beds, doctors, specialties, emergency_services

#### Water Resources CSV Format
Required fields: name, state, latitude, longitude, source_type (dam, lake, river, well, treatment_plant)
Optional fields: district, capacity, quality_index, serves_population, year_established

#### Electricity Infrastructure CSV Format
Required fields: name, state, latitude, longitude, power_type (hydro, thermal, nuclear, solar, wind, substation)
Optional fields: district, capacity, serves_areas, year_established

## Security Considerations

For production deployment:
1. Change the default admin credentials
2. Implement HTTPS
3. Ensure proper file upload validation
4. Consider implementing rate limiting
5. Regularly update dependencies

## License

This project is available for open use.

## Credits

Created by [Your Name/Organization] 