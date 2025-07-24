# 📦 Deployment Documentation - Nun's Dimsum

> Guide for deploying the Nun's Dimsum application in various environments.

## 📖 Contents

- [Overview](#overview)
- [Preparation](#preparation)
- [Deployment Steps](#deployment-steps)
- [Environment Configuration](#environment-configuration)
- [Post-Deployment Checklist](#post-deployment-checklist)
- [Troubleshooting](#troubleshooting)
- [Resources](#resources)

## 🌟 Overview

This document provides a detailed guide for deploying the Nun's Dimsum application using XAMPP and other relevant tools. It can be customized for specific hosting environments, whether local or cloud-based.

## ⚙️ Preparation

Ensure the following tools are installed and properly configured:
- **XAMPP**: With Apache and MySQL enabled
- **Git**: For version control
- **Composer**: For dependency management

### 🚨 Prerequisites

1. **Operating System**: Windows with admin rights
2. **Network Access**: Ensure access to necessary resources and repositories
3. **Database**: Prepared MySQL database as specified in `database.md`

## 🚀 Deployment Steps

### Step 1: Clone the Repository

```bash
# Clone repository to htdocs
cd D:\Programs\Xampp\htdocs

git clone https://repository.url/nuns-dimsum.git
```

### Step 2: Install Dependencies

```bash
# Navigate to project directory
cd nuns-dimsum

# Install PHP dependencies
composer install
```

### Step 3: Configure Environment

- **XAMPP**: Update `httpd.conf` as needed
- **Environment Variables**: Configure `.env` file in base directory:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=db_nunsdimsum
```

### Step 4: Deploy Database

```bash
# Execute the SQL script
mysql -u root -p db_nunsdimsum [sql\[complete_database_setup.sql``` )
```

### Step 5: Start Services

- Launch Apache and MySQL via XAMPP Control Panel
- Access the application via `http://localhost/nuns-dimsum`

## 🛠️ Environment Configuration

Ensure server settings as follows:

- **PHP Version**: 7.4 or higher
- **MySQL Version**: 8.0+
- **.htaccess**: Configure URL rewrites as needed

## 📋 Post-Deployment Checklist

1. **Verify Application**: Access main pages and confirm no errors
2. **Check Database**: Ensure all tables and initial data are correctly initialized
3. **Security Checks**: Validate secure configuration settings

## 🔥 Troubleshooting

1. **XAMPP Startup Failure**
   - Verify ports (80, 3306) are not in use by another process.
   - Check XAMPP configuration files for errors.

2. **Database Connection Error**
   - Ensure database credentials in `.env` match the setup.
   - Test MySQL connection separately.

3. **Permission Issues**
   - Check directory permissions especially when accessing via the web server.

4. **Missing Dependencies**
   - Run composer install again and ensure network connectivity.

## 📚 Resources

- [XAMPP Official Documentation](https://www.apachefriends.org/index.html)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Nun's Dimsum GitHub Repository](https://repository.url/nuns-dimsum)
 
