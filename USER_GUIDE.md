# 📘 Complete User Guide - Chicken Shop POS System

## 🔐 LOGIN CREDENTIALS

### 👨‍💼 ADMIN ACCOUNT
- **Email:** `admin@chickenshop.com`
- **Password:** `admin123`
- **Role:** Admin
- **Access:** Full system access

### 💰 CASHIER ACCOUNT
- **Email:** `cashier@chickenshop.com`
- **Password:** `cashier123`
- **Role:** Cashier
- **Access:** POS and limited functions

### 📝 PIN Login (Optional)
- Both accounts can have PINs set for quick login
- Set PIN in User Management (Admin only)
- Use PIN field on login page

---

## 🚀 HOW TO RUN THE APPLICATION

### **Windows (XAMPP)**

1. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL (if using MySQL instead of SQLite)

2. **Open Terminal/PowerShell:**
   ```powershell
   cd C:\Users\akila\Desktop\leravsl\laravel\laravel
   ```

3. **Install Dependencies (if not done):**
   ```powershell
   # Install PHP dependencies
   C:\xampp\php\php.exe composer install
   
   # Install JavaScript dependencies
   npm install
   ```

4. **Setup Environment:**
   ```powershell
   # Copy environment file
   copy .env.example .env
   
   # Generate application key
   C:\xampp\php\php.exe artisan key:generate
   ```

5. **Configure Database:**
   - Open `.env` file
   - Set: `DB_CONNECTION=sqlite`
   - Set: `DB_DATABASE=C:\Users\akila\Desktop\leravsl\laravel\laravel\database\database.sqlite`

6. **Create Database:**
   ```powershell
   # Create SQLite database file
   New-Item -Path database -Name database.sqlite -ItemType File
   ```

7. **Run Migrations:**
   ```powershell
   C:\xampp\php\php.exe artisan migrate
   ```

8. **Seed Database (Create Default Users):**
   ```powershell
   C:\xampp\php\php.exe artisan db:seed
   ```

9. **Build Frontend Assets:**
   ```powershell
   npm run build
   ```

10. **Start Server:**
    ```powershell
    C:\xampp\php\php.exe artisan serve
    ```

11. **Access Application:**
    - Open browser: `http://127.0.0.1:8000`
    - Login with admin or cashier credentials

### **Quick Start (Using Batch File)**
- Double-click `START_SERVER.bat` to start the server

---

## 📋 ALL FUNCTIONS GUIDE

### **ADMIN FUNCTIONS**

#### 1. **Dashboard** 📊
- View today's sales summary
- See expired items alerts
- View upcoming supplier payments
- Check low stock items
- View profit, expenses, and supplier payments
- **Location:** Main page after login

#### 2. **POS (Point of Sale)** 💰
- Search items by name, code, or barcode
- Add items to cart
- Apply discounts (within limit)
- Process sales with multiple payment methods
- Hold/resume bills
- Process returns/refunds
- **Location:** POS menu

#### 3. **Inventory Management** 📦

**Items:**
- Create, edit, deactivate items
- Set cost price, selling price
- Manage stock levels
- Set reorder levels
- Track expiry dates
- View stock history
- **Location:** Inventory → Items

**Categories:**
- Create product categories
- Edit category details
- Deactivate categories
- **Location:** Inventory → Categories

**GRN (Goods Received Note):**
- Create GRN when goods received
- Add items with quantities
- Update stock automatically
- Link to suppliers
- **Location:** Inventory → GRN

**Stock Adjustments:**
- Record damage/loss
- Stock take variance
- Adjust quantities
- **Location:** Inventory → Stock Adjustments

#### 4. **People Management** 👥

**Customers:**
- Create customer records
- View customer details
- Track outstanding balances
- View credit history
- **Location:** People → Customers

**Suppliers:**
- Add supplier information
- Track supplier payments
- View outstanding payables
- **Location:** People → Suppliers

**Users:**
- Create admin/cashier accounts
- Edit user details
- Set PINs
- Activate/deactivate users
- **Location:** People → Users

#### 5. **Credits/Loans** 💳
- View all customer credits
- Filter by status, customer, overdue
- Record payments
- View payment history
- **Location:** Credits menu

#### 6. **Expenses** 💰

**Expenses:**
- Record shop expenses
- Categorize expenses
- Track payment methods
- View expense history
- **Location:** Expenses → Expenses

**Expense Categories:**
- Create expense categories
- Edit categories
- **Location:** Expenses → Expense Categories

#### 7. **Reports** 📊

**Sales Report:**
- Daily, monthly, custom date range
- Breakdown by payment method
- Top selling items
- Export to CSV/Print
- **Location:** Reports → Sales Report

**Stock Report:**
- Current stock levels
- Stock valuation
- Movement history
- Export to CSV/Print
- **Location:** Reports → Stock Report

**Profit & Loss:**
- Calculate profit for period
- Include expenses
- Gross profit, net profit
- Export to CSV/Print
- **Location:** Reports → Profit & Loss

**Supplier Ledger:**
- Supplier balances
- Payment history
- Outstanding payables
- Export to CSV/Print
- **Location:** Reports → Supplier Ledger

**Expenses Report:**
- Expense summaries
- By category
- By date range
- Export to CSV/Print
- **Location:** Reports → Expenses Report

#### 8. **Sale Approvals** ✅
- Approve/reject sales requiring approval
- View pending approvals
- See discount approval requests
- **Location:** Approvals menu

#### 9. **Shift Summary** 📊
- View daily shift sales
- Breakdown by payment method
- Number of bills
- **Location:** Shift Summary menu

#### 10. **Settings** ⚙️
- Configure tax rates
- Set discount limits
- Configure invoice formats
- Set rounding rules
- Configure dashboard widgets
- Set session timeout
- Set cash refund limits
- **Location:** Settings menu

#### 11. **Audit Logs** 📝
- View all system actions
- Filter by user, action, date
- Track changes
- **Location:** Logs menu

#### 12. **Change Password** 🔒
- Change own password
- Enter current password
- Set new password
- **Location:** Password link in sidebar

---

### **CASHIER FUNCTIONS**

#### 1. **POS (Point of Sale)** 💰
- Search items by name, code, or barcode
- Add items to cart
- Apply discounts (within limit - requires admin approval if exceeded)
- Process sales with multiple payment methods:
  - Cash
  - Cheque (with cheque details)
  - Credit (with due date)
  - Card
- Hold/resume bills
- Process returns/refunds (with reason)
- Cancel sales (before payment or after with reason)
- **Location:** POS menu

#### 2. **Credits/Loans** 💳
- View customer credits (read-only)
- See outstanding balances
- View payment history
- **Location:** Credits menu

#### 3. **Shift Summary** 📊
- View own shift sales
- Breakdown by payment method
- Number of bills
- **Location:** Shift Summary menu

#### 4. **Change Password** 🔒
- Change own password
- **Location:** Password link in sidebar

---

## 🎯 KEY FEATURES

### **Sales & Payment Processing**
- ✅ Multiple payment methods (Cash, Cheque, Credit, Card)
- ✅ Split payments (part cash, part card)
- ✅ Discounts (with admin approval if exceeded)
- ✅ Tax calculation
- ✅ Rounding rules
- ✅ Hold/resume bills
- ✅ Returns/refunds
- ✅ Sale cancellation

### **Inventory Management**
- ✅ Real-time stock updates
- ✅ Stock adjustments
- ✅ Expiry date tracking
- ✅ Low stock alerts
- ✅ Stock history
- ✅ Barcode scanning support

### **Customer Management**
- ✅ Customer records
- ✅ Credit sales
- ✅ Outstanding balance tracking
- ✅ Payment due dates
- ✅ Payment recording

### **Reporting**
- ✅ Sales reports
- ✅ Stock reports
- ✅ Profit & Loss
- ✅ Supplier ledger
- ✅ Expenses reports
- ✅ CSV export
- ✅ Print functionality

### **Security**
- ✅ Role-based access control
- ✅ Password/PIN login
- ✅ Session timeout
- ✅ Audit logging
- ✅ CSRF protection

---

## 📱 HOW TO USE EACH FUNCTION

### **Creating a Sale (POS)**
1. Go to POS menu
2. Search for items (type name, code, or scan barcode)
3. Click item to add to cart
4. Adjust quantities if needed
5. (Optional) Select customer
6. (Optional) Apply discount
7. Select payment method
8. If Credit: Enter due date
9. If Cheque: Enter cheque details
10. Click "Complete Sale"
11. Receipt will be generated

### **Creating a GRN**
1. Go to Inventory → GRN
2. Click "Add New GRN"
3. Select supplier
4. Enter GRN date
5. Add items with quantities
6. Enter unit costs
7. Save
8. Stock will be updated automatically

### **Recording an Expense**
1. Go to Expenses → Expenses
2. Click "Add Expense"
3. Select expense category
4. Enter description
5. Enter amount
6. Select payment method
7. Enter date
8. Save

### **Viewing Reports**
1. Go to Reports menu
2. Select report type
3. Set date range (if applicable)
4. Click "Generate Report"
5. View results
6. Export to CSV or Print (if needed)

### **Managing Customers**
1. Go to People → Customers
2. Click "Add Customer"
3. Enter customer details:
   - Name
   - Phone
   - Email
   - Address
4. Save
5. Customer can now be used in credit sales

### **Approving Sales**
1. Go to Approvals menu
2. View pending approvals
3. Click "Approve" or "Reject"
4. Enter reason (if rejecting)
5. Save

---

## 🔧 SYSTEM SETTINGS

### **Tax Rate**
- Set percentage for tax calculation
- Applied to all sales

### **Discount Limits**
- Maximum discount cashier can apply
- Sales exceeding limit require admin approval

### **Invoice Format**
- Customize invoice number format
- Example: `INV-{YYYY}-{MM}-{DD}-{NNNN}`

### **Rounding Rules**
- None: No rounding
- Up: Round up
- Down: Round down
- Nearest: Round to nearest

### **Session Timeout**
- Auto-logout after inactivity
- Set in minutes

### **Dashboard Widgets**
- Show/hide profit widget
- Show/hide expenses widget
- Show/hide supplier payments widget
- Show/hide expired items widget
- Show/hide low stock widget

---

## 🆘 TROUBLESHOOTING

### **Cannot Login**
- Check email and password
- Ensure user is active
- Try resetting password
- Check audit logs

### **Page Not Loading**
- Clear browser cache
- Check server is running
- Check database connection
- Clear Laravel cache: `php artisan cache:clear`

### **419 Page Expired**
- Refresh page
- Login again
- Check session timeout settings

### **Stock Not Updating**
- Check GRN is completed
- Check sale is completed
- Verify stock movements in history

---

## 📞 SUPPORT

For issues or questions:
1. Check this guide
2. Review audit logs
3. Check system settings
4. Verify user permissions

---

**Last Updated:** System Complete  
**Version:** 1.0

