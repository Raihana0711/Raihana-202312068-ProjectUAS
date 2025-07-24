# 📖 Usage Guide - Nun's Dimsum

> Complete user guide for using the Nun's Dimsum restaurant management system.

## 📖 Table of Contents

- [Getting Started](#-getting-started)
- [Admin Guide](#-admin-guide)
- [User Guide](#-user-guide)
- [Features Overview](#-features-overview)
- [Best Practices](#-best-practices)
- [FAQ](#-faq)
- [Tips & Tricks](#-tips--tricks)

## 🚀 Getting Started

### First Time Setup

1. **Access the Application**
   ```
   http://localhost/backup.raihanna
   ```

2. **Default Login Credentials**
   ```
   Admin: admin / password
   User:  user / password
   ```

3. **Change Default Passwords**
   - Login as admin
   - Navigate to settings
   - Update password immediately

## 👨‍💼 Admin Guide

### 📊 Dashboard Overview

The admin dashboard provides a comprehensive overview of your restaurant operations:

- **Sales Statistics**: Real-time revenue and transaction data
- **Popular Items**: Most ordered menu items
- **Recent Orders**: Latest customer transactions
- **Customer Reviews**: Recent testimonials

### 🍜 Menu Management

#### Adding New Menu Items

1. **Navigate to Menu Management**
   - Click hamburger menu (☰)
   - Select "Kelola Menu"

2. **Add New Item**
   ```
   - Click "+ Tambah Menu Baru"
   - Fill required information:
     • Nama Menu: Item name
     • Kategori: Select category
     • Harga: Price in rupiah
     • Deskripsi: Item description
     • Gambar: Upload image (JPG/PNG, max 5MB)
   ```

3. **Save and Verify**
   - Click "Simpan Menu"
   - Verify item appears in menu list

#### Editing Menu Items

1. **Find Item to Edit**
   - Browse menu list
   - Click "Edit" button on desired item

2. **Update Information**
   - Modify fields as needed
   - Upload new image if required
   - Click "Update Menu"

#### Deleting Menu Items

1. **Select Item**
   - Find item in menu list
   - Click "Hapus" (Delete) button

2. **Confirm Deletion**
   - Read warning message
   - Confirm deletion
   - Item will be removed from system

### 💳 Transaction Management

#### Viewing Orders

1. **Access Transactions**
   - Click "Data Transaksi" in menu
   - View all customer orders

2. **Order Information**
   - Order ID and date
   - Customer information
   - Items ordered
   - Total amount
   - Payment status

#### Updating Order Status

1. **Select Order**
   - Click on transaction row
   - View order details

2. **Change Status**
   ```
   Available statuses:
   - Pending: New order, awaiting processing
   - Diproses: Order being prepared
   - Selesai: Order completed
   - Dibatalkan: Order cancelled
   ```

3. **Save Changes**
   - Update status
   - Add notes if needed
   - Save changes

### 📈 Reports & Analytics

#### Sales Reports

1. **Access Reports**
   - Navigate to "Laporan Penjualan"
   - Set date range for analysis

2. **Report Types**
   - **Daily Sales**: Revenue by day
   - **Menu Performance**: Best/worst selling items
   - **Customer Analytics**: Order patterns
   - **Financial Summary**: Profit and loss overview

3. **Export Options**
   - PDF format for printing
   - Excel format for further analysis

#### Popular Menu Analysis

- View most/least popular items
- Analyze sales trends
- Make informed menu decisions

### ⭐ Testimonial Management

#### Reviewing Customer Feedback

1. **Access Testimonials**
   - Go to "Testimoni" section
   - View all customer reviews

2. **Moderate Reviews**
   - Read customer feedback
   - Approve/reject testimonials
   - Respond to customer concerns

#### Managing Testimonial Display

- Choose which testimonials to showcase
- Hide inappropriate content
- Feature positive reviews

## 👤 User Guide

### 🏠 Homepage & Menu Browsing

#### Exploring Menu Items

1. **Browse Categories**
   - View available menu categories
   - Filter by food type
   - Use search functionality

2. **View Item Details**
   - Click on menu item
   - See description, price, and image
   - Read customer reviews

#### Using the Search Feature

- Enter keywords in search box
- Filter by category
- Sort by price or popularity

### 🛒 Shopping Cart

#### Adding Items to Cart

1. **Select Menu Item**
   - Choose desired quantity
   - Click "Tambah ke Keranjang"
   - Item added to cart

2. **Cart Management**
   - View cart contents via hamburger menu
   - Modify quantities
   - Remove unwanted items

#### Checkout Process

1. **Review Cart**
   - Verify items and quantities
   - Check total amount

2. **Place Order**
   - Click "Buat Pesanan Sekarang"
   - Confirm order details
   - Order submitted for processing

### 📜 Order History

#### Tracking Orders

1. **View Order History**
   - Access "Transaksi Saya" from menu
   - See all previous orders

2. **Order Status**
   ```
   Status meanings:
   - Pending: Order received, awaiting confirmation
   - Diproses: Kitchen preparing your order
   - Selesai: Order ready/completed
   - Dibatalkan: Order cancelled
   ```

#### Order Details

- View itemized list of ordered items
- See order date and total amount
- Track delivery/pickup status

### 💬 Testimonials

#### Leaving Reviews

1. **Access Testimonial Section**
   - Navigate to "Testimoni"
   - Click "Berikan Testimoni Anda"

2. **Write Review**
   ```
   Include:
   - Your name (editable)
   - Detailed review of experience
   - Optional: Photo URL
   - Submit testimonial
   ```

#### Viewing Reviews

- Read other customer experiences
- Get insights about menu items
- Make informed ordering decisions

## 🌟 Features Overview

### 🍔 Modern Navigation

#### Hamburger Menu Benefits

- **Space Efficient**: Clean, uncluttered interface
- **Mobile Friendly**: Optimal for all device sizes
- **Quick Access**: Easy navigation to all features
- **Visual Appeal**: Modern, professional appearance

#### User Information Panel

- Display user avatar with initials
- Show user role (Admin/Customer)
- Quick access to account settings
- Session information

### 🎨 Design System

#### Color Coding

- **Green Tones**: Primary navigation and success states
- **Pink Accents**: Call-to-action buttons and highlights
- **Status Colors**: Visual indicators for order status
- **Neutral Grays**: Text and background elements

#### Responsive Design

- **Mobile First**: Optimized for smartphones
- **Tablet Ready**: Perfect for tablet ordering
- **Desktop Enhanced**: Full features on larger screens
- **Cross-Browser**: Works on all modern browsers

### 🔒 Security Features

#### User Authentication

- Secure login system
- Password hashing with bcrypt
- Session management
- Role-based access control

#### Data Protection

- SQL injection prevention
- XSS attack protection
- Secure file uploads
- Input validation

## 💡 Best Practices

### For Administrators

1. **Regular Menu Updates**
   - Keep menu items current
   - Update prices as needed
   - Remove unavailable items
   - Add seasonal specials

2. **Order Management**
   - Process orders promptly
   - Update status regularly
   - Communicate with customers
   - Handle cancellations gracefully

3. **Customer Service**
   - Respond to testimonials
   - Address customer concerns
   - Monitor service quality
   - Gather feedback actively

4. **Data Management**
   - Regular database backups
   - Monitor system performance
   - Review sales reports
   - Analyze customer trends

### For Customers

1. **Account Security**
   - Use strong passwords
   - Keep login details private
   - Log out after sessions
   - Report suspicious activity

2. **Order Accuracy**
   - Review cart before checkout
   - Double-check quantities
   - Verify delivery details
   - Save favorite items

3. **Feedback**
   - Leave honest reviews
   - Report issues promptly
   - Suggest improvements
   - Recommend to friends

## ❓ FAQ

### General Questions

**Q: How do I reset my password?**
A: Contact the administrator or use the password reset feature if available.

**Q: Can I cancel an order?**
A: Orders can be cancelled while in "Pending" status. Contact admin for assistance.

**Q: How do I change my profile information?**
A: Currently, profile changes must be made through administrator.

### Technical Questions

**Q: The website is loading slowly. What should I do?**
A: Try clearing your browser cache, or contact technical support.

**Q: Images are not loading properly.**
A: Check your internet connection or try refreshing the page.

**Q: I'm getting error messages.**
A: Take a screenshot of the error and contact support with details.

### Order Questions

**Q: How long does order processing take?**
A: Processing time varies, typically 15-30 minutes for dimsum preparation.

**Q: Can I modify my order after placing it?**
A: Order modifications may be possible while status is "Pending". Contact admin immediately.

**Q: What payment methods are accepted?**
A: Currently supports cash, transfer, and e-wallet payments.

## 🎯 Tips & Tricks

### For Better User Experience

1. **Use Keyboard Shortcuts**
   - ESC key: Close hamburger menu
   - Enter: Submit forms
   - Tab: Navigate between fields

2. **Mobile Optimization**
   - Use landscape mode for better menu viewing
   - Tap and hold for additional options
   - Swipe gestures where supported

3. **Efficiency Tips**
   - Bookmark frequently used pages
   - Use browser's password manager
   - Keep order history for reordering

### For Administrators

1. **Bulk Operations**
   - Select multiple items when possible
   - Use batch processing for efficiency
   - Export data regularly for backup

2. **Monitoring**
   - Check dashboard daily
   - Review error logs weekly
   - Monitor customer feedback regularly

3. **Optimization**
   - Compress images before upload
   - Use descriptive menu names
   - Keep categories organized

## 📞 Getting Help

### Support Channels

- **Documentation**: Check docs folder for detailed guides
- **In-App Help**: Look for help icons (?) throughout the system
- **Technical Support**: Contact system administrator
- **User Community**: Join user forums or groups

### Reporting Issues

When reporting problems, include:
- What you were trying to do
- What happened instead
- Error messages (if any)
- Browser and device information
- Screenshots (if helpful)

---

## 🎉 Enjoy Using Nun's Dimsum!

This guide should help you make the most of the restaurant management system. Whether you're managing the restaurant or ordering delicious dimsum, the system is designed to provide an excellent experience.

For additional help or feature requests, don't hesitate to reach out to the support team.

**Happy dining!** 🥟👨‍🍳
