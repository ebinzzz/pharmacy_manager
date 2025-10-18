# Pharmacy Management System with Billing Software

A complete web-based Pharmacy Management System built with PHP, MySQL, HTML, and CSS. Features include inventory management, point-of-sale billing, optional customer tracking, and comprehensive reporting.

## 🚀 Features

### Core Modules
- **User Management**: Role-based access control (Admin, Pharmacist, Cashier)
- **Medicine Management**: Complete inventory tracking with batch numbers and expiry dates
- **Point of Sale (POS)**: Fast and intuitive billing system
- **Optional Customer Tracking**: Track purchases by phone number only (privacy-friendly)
- **Sales Records**: Comprehensive sales history and filtering
- **Reports & Analytics**: Business insights and performance metrics
- **Low Stock Alerts**: Automatic notifications for low inventory
- **Expiry Management**: Track and alert for near-expiry medicines

### Key Highlights
- ✅ Optional customer phone tracking (no mandatory registration)
- ✅ Real-time stock updates
- ✅ GST/Tax calculation
- ✅ Multiple payment methods (Cash, Card, UPI)
- ✅ Professional invoice generation
- ✅ Responsive design
- ✅ Print-friendly invoices and reports

## 📋 Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB
- Apache or Nginx web server
- 2GB RAM minimum
- 10GB storage

### PHP Extensions Required
- PDO
- PDO MySQL
- mbstring
- json

## 🔧 Installation

### Step 1: Download/Clone Files
Place all project files in your web server directory:
- For XAMPP: `C:\xampp\htdocs\pharmacy-management\`
- For WAMP: `C:\wamp64\www\pharmacy-management\`
- For Linux: `/var/www/html/pharmacy-management/`

### Step 2: Create Database
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Create a new database named `pharmacy_management`
3. Import the SQL file or run the database setup script provided

### Step 3: Configure Database Connection
Edit `config/database.php` and update the following:

```php
private $host = "localhost";
private $db_name = "pharmacy_management";
private $username = "root";
private $password = ""; // Your MySQL password
```

### Step 4: Set Permissions (Linux only)
```bash
sudo chmod -R 755 pharmacy-management/
sudo chown -R www-data:www-data pharmacy-management/
```

### Step 5: Access the Application
Open your browser and navigate to:
```
http://localhost/pharmacy-management/
```

## 🔐 Default Login Credentials

**Username:** admin  
**Password:** admin123

⚠️ **Important**: Change the default password after first login!

## 📁 Project Structure

```
pharmacy-management/
├── index.php                 # Main entry point
├── config/
│   ├── database.php         # Database configuration
│   └── config.php           # System constants & functions
├── includes/
│   └── sidebar.php          # Navigation sidebar
├── views/
│   ├── auth/
│   │   ├── login.php        # Login page
│   │   └── logout.php       # Logout handler
│   ├── dashboard/
│   │   └── index.php        # Main dashboard
│   ├── billing/
│   │   ├── pos.php          # Point of Sale
│   │   ├── invoice.php      # Invoice display/print
│   │   └── search_medicine.php  # Medicine search API
│   ├── medicines/
│   │   ├── list.php         # Medicine list
│   │   ├── add.php          # Add new medicine
│   │   ├── edit.php         # Edit medicine
│   │   └── view.php         # View medicine details
│   ├── sales/
│   │   └── list.php         # Sales records
│   ├── tracking/
│   │   └── search.php       # Customer tracking
│   └── reports/
│       └── index.php        # Reports & analytics
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   └── js/
│       └── main.js          # JavaScript functions
└── uploads/                 # Upload directory
```

## 💡 Usage Guide

### 1. Dashboard
After login, you'll see the main dashboard with:
- Today's sales summary
- Total medicines count
- Low stock alerts
- Expired medicines count
- Recent sales list

### 2. Point of Sale (POS) - Billing

**Step-by-step process:**

1. Click "Billing / POS" from the sidebar
2. Search for medicine by typing name or generic name
3. Click on medicine to add to cart
4. Adjust quantity using +/- buttons
5. **Optional:** Enter customer phone number (or skip)
6. Apply discount if needed
7. Select payment method
8. Click "Process Payment"
9. Invoice automatically generated
10. Print or save invoice

**Key Features:**
- Real-time medicine search
- Stock validation
- Automatic tax calculation
- Optional customer tracking
- Multiple payment methods

### 3. Medicine Management

**Add New Medicine:**
1. Go to "Medicines" → "Add New Medicine"
2. Fill required fields:
   - Medicine Name *
   - Category *
   - Batch Number *
   - MRP *
   - Stock Quantity *
3. Fill optional fields (Generic name, Manufacturer, etc.)
4. Click "Add Medicine"

**View/Edit Medicines:**
- Search and filter medicines
- View complete details
- Update stock levels
- Check expiry dates

### 4. Customer Tracking (Optional)

**How it works:**
- During billing, cashier can optionally enter phone number
- System automatically tracks:
  - Total purchases
  - Total amount spent
  - First and last purchase dates
- No customer registration required
- Privacy-friendly approach

**Search Customer:**
1. Go to "Customer Tracking"
2. Enter 10-digit phone number
3. View complete purchase history

### 5. Sales Records

**Features:**
- Filter by date range
- Filter by payment method
- View detailed invoices
- Print sales reports
- Track daily/monthly revenue

### 6. Reports & Analytics

**Available Reports:**
- Sales overview (Today, This Month)
- Inventory alerts (Low stock, Expired, Near expiry)
- Payment method breakdown
- Top 10 selling medicines
- Stock valuation

## 🎯 User Roles & Permissions

### Admin
- Full system access
- User management
- All module access
- System configuration

### Pharmacist
- Medicine management
- Purchase orders
- Billing/POS
- Reports viewing

### Cashier
- Billing/POS only
- Sales records
- Customer tracking (search only)

## 🔒 Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection
- Session management
- Role-based access control
- CSRF protection
- Session timeout (30 minutes)

## 🛠️ Customization

### Change Tax Rate
Edit `config/config.php`:
```php
define('TAX_RATE', 12); // Change to your GST/Tax rate
```

### Change Currency
Edit `config/config.php`:
```php
define('CURRENCY', '₹'); // Change to $, €, etc.
```

### Change Session Timeout
Edit `config/config.php`:
```php
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
```

## 📊 Database Tables

- **users**: System users and authentication
- **medicines**: Medicine inventory
- **categories**: Medicine categories
- **sales**: Sales transactions
- **sale_items**: Individual items in each sale
- **customer_tracking**: Optional phone-based tracking
- **suppliers**: Supplier information
- **purchases**: Purchase orders
- **purchase_items**: Purchase order items
- **prescriptions**: Prescription uploads

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify database credentials in `config/database.php`
- Ensure database exists

### Login Issues
- Clear browser cache
- Check database has default admin user
- Reset password using phpMyAdmin

### Permission Denied
- Check file permissions (755 for folders, 644 for files)
- Ensure uploads directory is writable

### Session Timeout Too Fast
- Increase `SESSION_TIMEOUT` in `config/config.php`
- Check PHP session settings

## 📝 Sample Data

The system comes with:
- 1 Admin user (admin/admin123)
- 6 Medicine categories
- 3 Sample medicines

## 🔄 Backup & Restore

### Backup Database
```bash
mysqldump -u root -p pharmacy_management > backup.sql
```

### Restore Database
```bash
mysql -u root -p pharmacy_management < backup.sql
```

## 📞 Support

For issues or questions:
1. Check this README first
2. Review database configuration
3. Check PHP error logs
4. Verify all requirements are met

## 🎓 Learning Resources

This project demonstrates:
- PHP MVC pattern
- PDO for database operations
- Prepared statements
- Session management
- Role-based access control
- AJAX for real-time search
- Responsive CSS design
- Print-friendly layouts

## ⚖️ License

This is an educational project. Free to use and modify for learning purposes.

## 🚀 Future Enhancements

Potential features to add:
- Barcode scanning
- SMS notifications
- Email invoices
- Advanced analytics
- Multi-branch support
- API integration
- Mobile app

---

**Version:** 1.0  
**Last Updated:** 2025  
**Built with:** PHP, MySQL, HTML5, CSS3, JavaScript