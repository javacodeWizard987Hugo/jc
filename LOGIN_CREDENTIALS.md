# 🔐 Login Credentials - Chicken Shop POS System

## 📋 Default User Accounts

After running `php artisan db:seed`, the following default users are created:

---

## 👨‍💼 ADMIN ACCOUNT

**Email:** `admin@chickenshop.com`  
**Password:** `admin123`  
**Role:** Admin  
**PIN:** Not set by default (can be set in user management)

**Access Level:**
- ✅ Full system access
- ✅ User management
- ✅ System settings
- ✅ All reports
- ✅ Inventory management
- ✅ Invoice cancellation
- ✅ Dashboard widget configuration

---

## 💰 CASHIER ACCOUNT

**Email:** `cashier@chickenshop.com`  
**Password:** `cashier123`  
**Role:** Cashier  
**PIN:** Not set by default (can be set in user management)

**Access Level:**
- ✅ POS/Billing interface
- ✅ Item search and sales
- ✅ Hold/resume bills
- ✅ Apply discounts (within limit)
- ✅ Process returns/refunds
- ✅ View shift summary
- ✅ Check stock availability
- ❌ No access to admin functions

---

## 👥 CUSTOMER/CLIENT ACCOUNTS

**Note:** The system does NOT have separate customer/client login accounts. 

Customers are managed as records in the database for:
- **Credit Sales** - Track customer credit purchases
- **Customer Information** - Name, contact, address
- **Outstanding Balances** - Track what customers owe

**Customer Management:**
- Customers are created by Admin through the Customer Management interface
- Customers are NOT users - they don't have login credentials
- Customer information is used for credit sales and tracking

**To Create a Customer:**
1. Login as Admin
2. Go to "Customers" menu
3. Click "Add New Customer"
4. Fill in customer details (name, phone, address, etc.)
5. Save

**Customer Features:**
- Can be assigned to credit sales
- Outstanding balances tracked
- Payment due dates tracked
- No login access to the system

---

## 🔑 PIN Login (Optional)

Both Admin and Cashier accounts can have PINs set for quick login:

**To Set PIN:**
1. Login as Admin
2. Go to "Users" menu
3. Edit the user
4. Set a 6-digit PIN
5. Save

**PIN Login:**
- Use the PIN field on login page
- Enter 6-digit PIN
- Quick access without typing email/password

---

## 🆕 Creating New Users

### As Admin:

1. Login as Admin
2. Navigate to **Users** → **Add New User**
3. Fill in:
   - Name
   - Email
   - Password
   - Role (Admin or Cashier)
   - PIN (optional, 6 digits)
4. Click **Save**

---

## 🔄 Resetting Passwords

### Admin can reset any user's password:
1. Login as Admin
2. Go to **Users** menu
3. Click on the user
4. Click **Edit**
5. Enter new password
6. Save

### Users can change their own password:
1. Login to the system
2. Click on your name (top right)
3. Select **Change Password**
4. Enter current password
5. Enter new password
6. Confirm new password
7. Save

---

## 📝 Quick Reference

| Account Type | Email | Password | PIN | Login Access |
|-------------|-------|----------|-----|--------------|
| **Admin** | admin@chickenshop.com | admin123 | Not set | ✅ Full Access |
| **Cashier** | cashier@chickenshop.com | cashier123 | Not set | ✅ POS Only |
| **Customer** | N/A | N/A | N/A | ❌ No Login |

---

## 🚨 Security Notes

1. **Change Default Passwords** - Immediately change default passwords after first login
2. **Strong Passwords** - Use strong passwords (min 8 characters, mix of letters/numbers)
3. **PIN Security** - Keep PINs confidential
4. **User Management** - Deactivate unused accounts
5. **Audit Logs** - All login/logout actions are logged

---

## 🔍 Finding Login Information

If you forget login credentials:

1. **Check Database:**
   ```sql
   SELECT email, role FROM users WHERE is_active = 1;
   ```

2. **Reset via Seeder:**
   ```bash
   php artisan db:seed --class=DatabaseSeeder
   ```
   This will reset admin and cashier passwords to defaults.

3. **Create New Admin (if needed):**
   ```bash
   php artisan tinker
   ```
   Then:
   ```php
   User::create([
       'name' => 'New Admin',
       'email' => 'newadmin@chickenshop.com',
       'password' => Hash::make('newpassword123'),
       'role' => 'admin',
       'is_active' => true
   ]);
   ```

---

## 📞 Support

If you need help with login:
1. Check that the user account is active (`is_active = true`)
2. Verify the email is correct
3. Try resetting password
4. Check audit logs for login attempts

---

**Last Updated:** System Initialization  
**Default Credentials:** Set by DatabaseSeeder

