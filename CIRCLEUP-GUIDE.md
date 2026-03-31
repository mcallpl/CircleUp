# CircleUp E-Commerce Store — Complete Setup Guide

## Overview

CircleUp is a fully functional e-commerce platform built for selling premium apparel and accessories (T-shirts, caps, sweatshirts, pants, shoes, etc.). It features:

- **Admin Dashboard** — Upload products, manage inventory, view orders
- **Beautiful Storefront** — Customers browse and purchase products
- **Stripe Integration** — Secure payment processing
- **Database Management** — MySQL backend with complete order tracking
- **Auto-Sync** — Files from your Mac sync automatically to Empire
- **GitHub Integration** — All changes are automatically committed and pushed

---

## 🚀 Quick Start

### Access Points

**Public Store:**
- https://www.peoplestar.com/CircleUp/
- http://64.227.108.128/CircleUp/

**Admin Dashboard:**
- https://www.peoplestar.com/CircleUp/admin/login.php
- Login: `mcallpl` / `amazing` (from vault)

### Credentials

All credentials are stored in the vault and auto-loaded:
- **Database:** mcallpl / REDACTED
- **Stripe Secret:** REDACTED_STRIPE_KEY
- **Stripe Publishable:** REDACTED_STRIPE_PK

---

## 📦 Admin Features

### Adding Products

1. Go to `/CircleUp/admin/login.php`
2. Login with `mcallpl`
3. Click "Products" → "+ Add Product"
4. Fill in:
   - **Product Name** (required) — e.g., "Crew Neck T-Shirt"
   - **Price** (required) — $29.99
   - **Category** (required) — Choose from: T-Shirts, Caps, Sweatshirts, Pants, Shoes, Hoodies, Accessories
   - **Description** — Marketing copy
   - **Image** — Upload JPG, PNG, or WebP (max 5MB)

### Adding Variants

After creating a product, add **sizes and colors**:
- **Size:** XS, S, M, L, XL, 2XL, 3XL, One Size
- **Color:** Black, White, Navy, Gray, Red, Blue, Green, Yellow, Purple, Pink, Orange, Brown
- **Stock:** How many units available

Each variant automatically generates a SKU for inventory tracking.

**Example:**
```
Product: "CircleUp Navy Hoodie"
Price: $59.99
Variants:
  - Size: M, Color: Navy, Stock: 50
  - Size: L, Color: Navy, Stock: 75
  - Size: XL, Color: Navy, Stock: 60
  - Size: M, Color: Black, Stock: 40
```

### Managing Orders

- **Dashboard** shows real-time stats: total products, orders, revenue, pending orders
- **Recent Orders** tab displays latest purchases
- Click any order to view details and update status:
  - Pending → Completed → Shipped
  - Cancel orders as needed

### Image Upload & Formatting

**Auto-Optimization:**
- Images are stored in `/CircleUp/uploads/`
- Automatically served via CDN cache (1-year expiration)
- Max size: 5MB (recommended: 1-2MB for web)
- Formats: JPG, PNG, WebP (WebP recommended for smaller file size)

**Best Practices:**
- Use square images (1:1 aspect ratio) for product cards
- Minimum 500x500px, ideal 1000x1000px or higher
- Show product clearly without busy backgrounds

---

## 🛍️ Customer Experience

### Browse & Search

**Homepage** shows:
- All products organized by category
- Search bar to find specific items
- Category filters (T-Shirts, Caps, etc.)
- Sort options: Newest, Price (low-to-high), Popular

**Product Cards Display:**
- Product image
- Product name
- Description (truncated, 2 lines max)
- Price
- "Add to Cart" button

### Checkout

1. Click "Add" on any product
2. Select size/color variant if available
3. Set quantity
4. Click "Checkout" → redirected to Stripe
5. Enter payment info (Stripe handles security)
6. Receive order confirmation email
7. Chip gets SMS alert via Twilio (optional, not yet configured)

---

## 🔄 Auto-Sync & Deployment

### How It Works

1. **Mac Development:**
   - You edit files in `/Users/chipmcallister/Projects/CircleUp`
   - Save locally, test locally

2. **Auto-Sync to Empire:**
   - Changes sync via `rsync` to `/var/www/html/CircleUp`
   - Exclude: node_modules, .git, .DS_Store, build artifacts

3. **GitHub Backup:**
   - Every change auto-commits to GitHub
   - All history preserved
   - Easy rollback if needed

### Manual Sync (If Auto-Sync Not Running)

**From Mac Terminal:**
```bash
cd /Users/chipmcallister/Projects/CircleUp
./deploy-sync.sh
```

Or use `rsync` directly:
```bash
rsync -avz --delete \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.DS_Store' \
  . root@64.227.108.128:/var/www/html/CircleUp/
```

**Git Push (Manual):**
```bash
cd /Users/chipmcallister/Projects/CircleUp
git add -A
git commit -m "Update products"
git push origin main
```

---

## 💳 Stripe Integration

### Current Setup

- **Payment Method:** Credit/debit cards via Stripe
- **Live Keys:** Using production (live) Stripe account
- **Webhook Endpoint:** `/CircleUp/api/webhook.php`
- **Checkout:** Secure Stripe Checkout page

### How Payments Flow

1. Customer clicks "Checkout"
2. Redirected to Stripe-hosted checkout page
3. Enters card details (PCI compliant)
4. Payment processed
5. Stripe calls webhook on Empire
6. Order status updated to "completed"
7. Confirmation email sent

### Testing Payments

Use Stripe test cards:
```
Visa: 4242 4242 4242 4242
Exp: Any future date (e.g., 12/25)
CVC: Any 3 digits (e.g., 123)
```

### Monitoring Payments

Check Stripe Dashboard:
- https://dashboard.stripe.com/
- Login with your Stripe account
- View all transactions, refunds, disputes

---

## 📊 Database Schema

### Products Table
```sql
products
├── id (INT, auto-increment)
├── name (VARCHAR 255)
├── description (TEXT)
├── price (DECIMAL 10,2)
├── category (VARCHAR 100)
├── image_url (VARCHAR 500)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Variants Table (Sizes, Colors, Stock)
```sql
variants
├── id (INT, auto-increment)
├── product_id (INT, FK → products)
├── size (VARCHAR 50)
├── color (VARCHAR 50)
├── stock (INT)
├── sku (VARCHAR 100, unique)
└── created_at (TIMESTAMP)
```

### Orders Table
```sql
orders
├── id (INT, auto-increment)
├── order_number (VARCHAR 50, unique)
├── stripe_payment_intent_id (VARCHAR 255)
├── customer_email (VARCHAR 255)
├── customer_name (VARCHAR 255)
├── total_amount (DECIMAL 10,2)
├── status (ENUM: pending, completed, shipped, cancelled)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Order Items Table
```sql
order_items
├── id (INT, auto-increment)
├── order_id (INT, FK → orders)
├── product_id (INT, FK → products)
├── variant_id (INT, FK → variants)
├── quantity (INT)
├── price (DECIMAL 10,2)
└── created_at (TIMESTAMP)
```

### Admins Table (Access Control)
```sql
admins
├── id (INT, auto-increment)
├── username (VARCHAR 100, unique)
├── password_hash (VARCHAR 255)
├── email (VARCHAR 255)
└── created_at (TIMESTAMP)
```

---

## 🔒 Security

### Best Practices

1. **Credentials:** All sensitive keys stored in vault (config.php, not committed)
2. **Passwords:** Bcrypt hashed (password_verify for auth)
3. **SQL Injection:** Prepared statements used throughout
4. **File Upload:** Type/size validation, stored outside webroot
5. **Admin Access:** Session-based, 1-hour expiration
6. **Stripe:** PCI DSS compliant, no card data stored locally

### Sensitive Files to Exclude from Git

Already in `.gitignore`:
- `.env` files
- `config.php`
- `vendor/` (Composer dependencies)
- `node_modules/`
- `uploads/` (user-generated content)

---

## 🚨 Troubleshooting

### Admin Login Not Working
- Check database: `mysql -u mcallpl -p'REDACTED' circleup`
- Verify admin user exists: `SELECT * FROM admins WHERE username = 'mcallpl';`
- Reset password: `UPDATE admins SET password_hash = '$2y$12$...' WHERE username = 'mcallpl';`

### Products Not Showing on Storefront
- Check database for products: `SELECT COUNT(*) FROM products;`
- Verify images uploaded: `/var/www/html/CircleUp/uploads/`
- Check browser console for JS errors

### Stripe Checkout Not Working
- Verify Stripe keys in config.php
- Check webhook endpoint: `POST /CircleUp/api/webhook.php`
- Review Stripe Dashboard for failed events

### Sync/Deploy Issues
- Ensure SSH key configured for GitHub
- Check Empire disk space: `df -h /var/www/html/`
- Verify permissions: `ls -la /var/www/html/CircleUp/`

---

## 📞 Support Contacts

- **Stripe Support:** https://support.stripe.com/
- **GitHub Issues:** https://github.com/chipmcallister/circleup/issues
- **Empire Server:** root@64.227.108.128 (SSH)

---

## 📅 Roadmap (Future Features)

- [ ] Product detail pages with full images
- [ ] Shopping cart (persistent, local storage)
- [ ] User accounts & order history
- [ ] Email confirmation & order tracking
- [ ] SMS notifications (Twilio)
- [ ] Inventory sync to analytics
- [ ] Advanced analytics dashboard
- [ ] Mobile app integration
- [ ] Gift cards & promotions
- [ ] Wishlist functionality

---

**Last Updated:** 2026-03-31  
**CircleUp Version:** 1.0.0  
**Status:** ✅ Live & Operational
