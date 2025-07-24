# 💿 Installation Guide - Nun's Dimsum

> Complete step-by-step installation guide for setting up Nun's Dimsum restaurant management system.

## 📖 Table of Contents

- [System Requirements](#-system-requirements)
- [Prerequisites](#-prerequisites)
- [Installation Methods](#-installation-methods)
- [Step-by-Step Installation](#-step-by-step-installation)
- [Configuration](#-configuration)
- [First Run](#-first-run)
- [Troubleshooting](#-troubleshooting)
- [Next Steps](#-next-steps)

## 💻 System Requirements

### Minimum Requirements
- **OS**: Windows 10, macOS 10.15+, or Linux Ubuntu 18.04+
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 500MB free space
- **Network**: Internet connection for initial setup

### Software Requirements
- **Web Server**: Apache 2.4+
- **PHP**: 8.0+ with extensions: mysqli, gd, curl, mbstring
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

## 🔧 Prerequisites

Before installation, ensure you have:

1. **XAMPP** (recommended) or equivalent LAMP/WAMP stack
2. **Git** (optional, for repository cloning)
3. **Text Editor/IDE** (VSCode, PHPStorm, etc.)
4. **Admin/Root access** to your system

### Installing XAMPP

1. **Download XAMPP**
   ```bash
   # Windows
   https://www.apachefriends.org/download.html
   
   # Download the latest version for your OS
   ```

2. **Install XAMPP**
   - Run installer as administrator
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Choose installation directory (default: C:\xampp)
   - Complete installation

3. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL modules
   - Verify green status indicators

## 📦 Installation Methods

Choose one of the following installation methods:

### Method 1: Direct Download (Recommended)
```bash
# Download project files directly
# Extract to htdocs/nuns-dimsum
```

### Method 2: Git Clone
```bash
# Clone from repository
cd D:\Programs\Xampp\htdocs
git clone https://github.com/yourusername/nuns-dimsum.git
```

### Method 3: Manual Setup
```bash
# Create project structure manually
# Copy files step by step
```

## 🛠️ Step-by-Step Installation

### Step 1: Download and Extract Files

```bash
# Option A: Direct download
# 1. Download ZIP from GitHub
# 2. Extract to D:\Programs\Xampp\htdocs\nuns-dimsum

# Option B: Git clone
cd D:\Programs\Xampp\htdocs
git clone https://github.com/yourusername/nuns-dimsum.git
cd nuns-dimsum
```

### Step 2: Verify File Structure

```
nuns-dimsum/
├── src/
│   ├── includes/
│   └── views/
├── public/
│   └── uploads/
├── database/
│   └── complete_database_setup.sql
├── docs/
├── index.php
└── README.md
```

### Step 3: Setup Database

1. **Open phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Create Database**
   ```sql
   CREATE DATABASE db_nunsdimsum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Database Structure**
   - Select `db_nunsdimsum` database
   - Go to `Import` tab
   - Choose file: `database/complete_database_setup.sql`
   - Click `Go` to import

4. **Verify Tables**
   ```sql
   USE db_nunsdimsum;
   SHOW TABLES;
   -- Should show 10 tables
   ```

### Step 4: Configure Application

1. **Database Configuration**
   
   Edit `src/includes/config.php`:
   ```php
   <?php
   // Database configuration
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Your MySQL password
   define('DB_NAME', 'db_nunsdimsum');
   
   // Application settings
   define('BASE_URL', 'http://localhost/nuns-dimsum/');
   define('UPLOAD_PATH', 'public/uploads/');
   ?>
   ```

2. **Set Directory Permissions**
   ```bash
   # Windows (via Command Prompt as Admin)
   icacls "D:\Programs\Xampp\htdocs\nuns-dimsum\public\uploads" /grant Everyone:F
   
   # Linux/macOS
   chmod -R 755 public/uploads/
   chmod -R 777 logs/
   ```

3. **Configure Apache (Optional)**
   
   Add to `httpd.conf` for custom domain:
   ```apache
   <VirtualHost *:80>
       ServerName nunsdimsum.local
       DocumentRoot "D:/Programs/Xampp/htdocs/nuns-dimsum"
       DirectoryIndex index.php
   </VirtualHost>
   ```

   Add to Windows `hosts` file:
   ```
   127.0.0.1 nunsdimsum.local
   ```

### Step 5: Test Installation

1. **Access Application**
   ```
   http://localhost/nuns-dimsum
   # or
   http://nunsdimsum.local (if virtual host configured)
   ```

2. **Verify Landing Page**
   - Should see Nun's Dimsum homepage
   - All images should load correctly
   - Menu items should display

3. **Test Login**
   
   Default credentials:
   ```
   Admin: admin / password
   User:  user / password
   ```

## ⚙️ Configuration

### Environment Variables

Create `.env` file in root directory:
```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=db_nunsdimsum

# Application
APP_ENV=development
APP_DEBUG=true
BASE_URL=http://localhost/nuns-dimsum/

# Upload settings
MAX_FILE_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif

# Session settings
SESSION_TIMEOUT=3600
```

### Security Configuration

1. **Change Default Passwords**
   ```sql
   -- Update admin password
   UPDATE user SET password = '$2y$12$newhashedpassword' WHERE username = 'admin';
   ```

2. **Configure File Uploads**
   ```php
   // In config.php
   ini_set('upload_max_filesize', '5M');
   ini_set('post_max_size', '5M');
   ini_set('max_execution_time', 300);
   ```

3. **Setup .htaccess**
   ```apache
   # In public/.htaccess
   <Files "*.php">
       Order Deny,Allow
       Deny from all
   </Files>
   
   # Only allow image files
   <FilesMatch "\.(jpg|jpeg|png|gif)$">
       Order Allow,Deny
       Allow from all
   </FilesMatch>
   ```

## 🎯 First Run

### 1. Initial Setup Checklist

- [ ] XAMPP services running (Apache + MySQL)
- [ ] Database imported successfully
- [ ] File permissions set correctly
- [ ] Configuration files updated
- [ ] Default login working

### 2. Admin Setup

1. **Login as Admin**
   ```
   Username: admin
   Password: password
   ```

2. **Change Admin Password**
   - Go to Admin Panel
   - Navigate to Profile/Settings
   - Update password

3. **Configure Basic Settings**
   - Upload restaurant logo
   - Set restaurant information
   - Configure categories
   - Add initial menu items

### 3. Test User Experience

1. **Create Test User Account**
2. **Test Menu Browsing**
3. **Test Cart Functionality**
4. **Test Order Process**
5. **Test Testimonial Feature**

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Could not connect to MySQL
```
**Solution:**
- Verify MySQL service is running
- Check database credentials in config.php
- Ensure database exists

#### File Upload Issues
```
Error: Failed to upload image
```
**Solution:**
```bash
# Check permissions
chmod 777 public/uploads/

# Check PHP settings
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
```

#### Apache Won't Start
```
Error: Apache shutdown unexpectedly
```
**Solution:**
- Check port 80 is not in use
- Review Apache error logs
- Verify httpd.conf syntax

#### Page Not Found (404)
```
The requested URL was not found on this server
```
**Solution:**
- Verify .htaccess configuration
- Check Apache mod_rewrite is enabled
- Confirm file paths are correct

### Debug Mode

Enable debug mode for detailed error reporting:

```php
// In config.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Application debug
define('APP_DEBUG', true);
```

### Log Files

Check these log files for errors:
- `logs/activity.log` - Application logs
- `xampp/apache/logs/error.log` - Apache errors
- `xampp/mysql/data/*.err` - MySQL errors

## ✅ Next Steps

After successful installation:

1. **Read Documentation**
   - [Usage Guide](usage.md)
   - [Database Documentation](database.md)
   - [Deployment Guide](deployment.md)

2. **Customize Application**
   - Update branding and colors
   - Add your menu items
   - Configure business settings

3. **Security Hardening**
   - Change all default passwords
   - Configure SSL/HTTPS
   - Set up regular backups
   - Review file permissions

4. **Performance Optimization**
   - Enable caching
   - Optimize database queries
   - Compress images
   - Minify CSS/JS

## 📞 Support

Need help? Here are your options:

- **Documentation**: Check other files in `/docs/`
- **Issues**: Create GitHub issue
- **Community**: Join our Discord server
- **Email**: support@nunsdimsum.com

---

## 🎉 Congratulations!

You've successfully installed Nun's Dimsum! 🥟

Your restaurant management system is now ready to use. Start by customizing the menu and exploring all the features.

**Default URLs:**
- **Homepage**: http://localhost/nuns-dimsum
- **Admin Panel**: http://localhost/nuns-dimsum/index.php?page=admin
- **User Dashboard**: http://localhost/nuns-dimsum/index.php?page=user

**Happy cooking!** 👨‍🍳👩‍🍳
