# 🚀 How to Run the POS System - Complete Guide

This guide will walk you through setting up and running the Laravel POS system from scratch.

---

## 📋 Prerequisites

Before you start, make sure you have these installed on your computer:

1. **PHP 8.2 or higher**
   - Download from: https://www.php.net/downloads.php
   - Or use XAMPP/WAMP which includes PHP

2. **Composer** (PHP package manager)
   - Download from: https://getcomposer.org/download/

3. **Node.js and npm** (for frontend assets)
   - Download from: https://nodejs.org/

4. **Database** (MySQL, PostgreSQL, or SQLite)
   - MySQL: https://dev.mysql.com/downloads/
   - Or use XAMPP/WAMP which includes MySQL
   - SQLite is included with PHP (easiest option)

5. **Code Editor** (optional but recommended)
   - VS Code: https://code.visualstudio.com/

---

## 🛠️ Step-by-Step Installation

### Step 1: Open Terminal/Command Prompt

- **Windows**: Press `Win + R`, type `cmd`, press Enter
- **Mac/Linux**: Open Terminal

Navigate to your project folder:
```bash
cd C:\Users\akila\Desktop\leravsl\laravel\laravel
```

---

### Step 2: Install PHP Dependencies

Run this command to install all PHP packages:
```bash
composer install
```

**Note**: If you get an error about `composer` not found:
- Make sure Composer is installed and added to your system PATH
- Or use: `php composer.phar install` (if you downloaded composer.phar)

**Wait for it to complete** - this may take a few minutes.

---

### Step 3: Install JavaScript Dependencies

Run this command to install frontend packages:
```bash
npm install
```

**Wait for it to complete** - this may take a few minutes.

---

### Step 4: Create Environment File

Copy the example environment file:
```bash
copy .env.example .env
```

**On Mac/Linux**, use:
```bash
cp .env.example .env
```

---

### Step 5: Generate Application Key

This creates a unique encryption key for your application:
```bash
php artisan key:generate
```

---

### Step 6: Configure Database

Open the `.env` file in a text editor and configure your database.

#### Option A: SQLite (Easiest - No setup needed)

Edit `.env` and set:
```env
DB_CONNECTION=sqlite
DB_DATABASE=C:\Users\akila\Desktop\leravsl\laravel\laravel\database\database.sqlite
```

Then create the SQLite database file:
```bash
type nul > database\database.sqlite
```

**On Mac/Linux**:
```bash
touch database/database.sqlite
```

#### Option B: MySQL (If you have MySQL installed)

Edit `.env` and set:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chicken_shop_pos
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Important**: Create the database first:
1. Open MySQL (phpMyAdmin or MySQL command line)
2. Create a new database named `chicken_shop_pos`

---

### Step 7: Run Database Migrations

This creates all the database tables:
```bash
php artisan migrate
```

**If you get errors**:
- Make sure your database is configured correctly in `.env`
- For MySQL: Make sure the database exists
- For SQLite: Make sure the database file exists

---

### Step 8: Seed the Database

This creates default users and settings:
```bash
php artisan db:seed
```

This will create:
- **Admin user**: `admin@chickenshop.com` / Password: `admin123`
- **Cashier user**: `cashier@chickenshop.com` / Password: `cashier123`
- Default system settings

---

### Step 9: Build Frontend Assets

Compile CSS and JavaScript files:
```bash
npm run build
```

**Alternative** (for development with auto-reload):
```bash
npm run dev
```

---

### Step 10: Start the Development Server

Run the Laravel development server:
```bash
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

### Step 11: Access the Application

Open your web browser and go to:
```
http://127.0.0.1:8000
```

Or:
```
http://localhost:8000
```

---

## 🔐 Default Login Credentials

### Admin Account
- **Email**: `admin@chickenshop.com`
- **Password**: `admin123`
- **PIN**: Not set by default (can be set in Admin > Users)

### Cashier Account
- **Email**: `cashier@chickenshop.com`
- **Password**: `cashier123`
- **PIN**: Not set by default (can be set in Admin > Users)

---

## 🎯 Quick Start Checklist

Use this checklist to make sure everything is set up:

- [ ] PHP installed (check with: `php -v`)
- [ ] Composer installed (check with: `composer -v`)
- [ ] Node.js installed (check with: `node -v`)
- [ ] Dependencies installed (`composer install` and `npm install`)
- [ ] `.env` file created and configured
- [ ] Application key generated (`php artisan key:generate`)
- [ ] Database configured in `.env`
- [ ] Migrations run (`php artisan migrate`)
- [ ] Database seeded (`php artisan db:seed`)
- [ ] Assets built (`npm run build`)
- [ ] Server started (`php artisan serve`)
- [ ] Application accessible in browser

---

## 🐛 Troubleshooting

### Problem: "composer: command not found"
**Solution**: 
- Install Composer from https://getcomposer.org/
- Or use: `php composer.phar` instead of `composer`

### Problem: "php: command not found"
**Solution**:
- Install PHP or use XAMPP/WAMP
- Add PHP to your system PATH
- Or use full path: `C:\xampp\php\php.exe artisan serve`

### Problem: Database connection error
**Solution**:
- Check `.env` file has correct database credentials
- For MySQL: Make sure MySQL is running
- For MySQL: Make sure the database exists
- For SQLite: Make sure the database file exists

### Problem: "npm: command not found"
**Solution**:
- Install Node.js from https://nodejs.org/
- Restart your terminal after installation

### Problem: Port 8000 already in use
**Solution**:
- Use a different port: `php artisan serve --port=8001`
- Or stop the other application using port 8000

### Problem: Migration errors
**Solution**:
- Make sure database is configured correctly
- Try: `php artisan migrate:fresh` (⚠️ This will delete all data!)
- Check database permissions

---

## 📝 Daily Usage

### Starting the Application

1. Open terminal/command prompt
2. Navigate to project folder:
   ```bash
   cd C:\Users\akila\Desktop\leravsl\laravel\laravel
   ```
3. Start the server:
   ```bash
   php artisan serve
   ```
4. Open browser: `http://localhost:8000`

### Stopping the Application

Press `Ctrl + C` in the terminal where the server is running.

---

## 🔄 Updating the Application

If you pull new changes from Git:

1. Update dependencies:
   ```bash
   composer install
   npm install
   ```

2. Run new migrations:
   ```bash
   php artisan migrate
   ```

3. Rebuild assets:
   ```bash
   npm run build
   ```

---

## 🌐 Running on Different Port

To run on a different port (e.g., 8080):
```bash
php artisan serve --port=8080
```

Then access: `http://localhost:8080`

---

## 📦 Production Deployment

For production, you'll need to:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Optimize the application:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Use a proper web server (Apache/Nginx) instead of `php artisan serve`

---

## 🆘 Need Help?

If you encounter any issues:

1. Check the error message in the terminal
2. Check Laravel logs: `storage/logs/laravel.log`
3. Make sure all prerequisites are installed
4. Verify database configuration in `.env`
5. Try running migrations again: `php artisan migrate:fresh --seed`

---

## ✅ Success!

If you see the login page when you open `http://localhost:8000`, congratulations! Your POS system is running! 🎉

Login with the admin credentials and start exploring the system.

---

## 📚 Next Steps

After logging in:

1. **As Admin**:
   - Go to Settings and configure system preferences
   - Add Categories
   - Add Items/Products
   - Add Suppliers
   - Create more users if needed

2. **As Cashier**:
   - Go to POS to start processing sales
   - View Shift Summary for daily reports

Enjoy using your POS system! 🚀

