# 🛍️ CircleUp — Premium E-Commerce Store

A complete, production-ready e-commerce platform for selling apparel and accessories with admin management, Stripe payments, and automatic sync.

---

## 🚀 Live Now

**Storefront:** https://www.peoplestar.com/CircleUp/  
**Admin Panel:** https://www.peoplestar.com/CircleUp/admin/login.php  
**Status:** ✅ Fully Operational

---

## 📋 Quick Features

- **Admin Dashboard** — Upload products, manage inventory, view orders & revenue
- **Beautiful Storefront** — Browse by category, search, responsive design
- **Product Management** — Images, pricing, sizes, colors, stock tracking
- **Stripe Integration** — Secure checkout, live payments
- **Database Backed** — MySQL with complete order history
- **Auto-Sync** — Changes from Mac automatically deploy to server
- **GitHub Integration** — All changes tracked and committed
- **Security** — Bcrypt passwords, prepared SQL statements, file validation

---

## 🏃 Getting Started

### 1. Admin Login

Visit: **https://www.peoplestar.com/CircleUp/admin/login.php**

```
Username: mcallpl
Password: [from vault]
```

### 2. Add Your First Product

1. Click "Products" → "+ Add Product"
2. Fill in:
   - **Name:** CircleUp Crew Neck Tee
   - **Price:** $29.99
   - **Category:** T-Shirts
   - **Description:** Premium cotton blend
   - **Image:** Upload product photo (JPG, PNG, WebP)
3. Add Variants:
   - **Size:** S, M, L, XL
   - **Color:** Black, White, Navy
   - **Stock:** 50 units per variant
4. Click "Create Product"

### 3. Storefront

Visit: **https://www.peoplestar.com/CircleUp/**

- Products appear automatically
- Customers browse, search, add to cart
- Checkout via Stripe
- You get real-time order updates

---

## 📁 Directory Structure

```
/var/www/html/CircleUp/
├── admin/                    # Admin panel
│   ├── login.php            # Admin login page
│   ├── dashboard.php        # Dashboard & stats
│   ├── product-form.php     # Add/edit products
│   ├── auth.php             # Authentication
│   └── logout.php           # Logout
├── api/                      # API endpoints
│   ├── checkout.php         # Stripe checkout
│   └── webhook.php          # Stripe webhook
├── store/                    # Public storefront
│   └── index.php            # Homepage & products
├── uploads/                  # Product images
├── vendor/                   # Composer libraries (Stripe SDK)
├── config.php               # Database & Stripe config
├── .htaccess                # URL routing
├── composer.json            # Dependencies
├── CIRCLEUP-GUIDE.md        # Full documentation
├── SETUP-CHECKLIST.md       # Setup guide
└── README.md                # This file
```

---

## 💳 Stripe Payments

### How It Works

1. Customer clicks "Checkout" on storefront
2. Redirected to Stripe Checkout page
3. Enters payment info securely
4. Payment processed
5. Webhook updates order status
6. Confirmation email sent

### Test Payments

Use Stripe test card:
```
Card: 4242 4242 4242 4242
Exp: 12/25 (any future date)
CVC: 123 (any 3 digits)
```

### Stripe Dashboard

Monitor all transactions: https://dashboard.stripe.com/

---

## 🗄️ Database

**Database:** `circleup`  
**User:** `mcallpl`  
**Password:** `REDACTED`

### Tables

- **products** — Name, price, category, image, description
- **variants** — Size, color, stock, SKU per product
- **orders** — Order number, customer, amount, status
- **order_items** — Individual items in each order
- **admins** — Admin users and access control
- **audit_log** — Admin actions for compliance

---

## 🔄 Auto-Sync Setup

**From your Mac:**

```bash
cd /Users/chipmcallister/Projects/CircleUp
chmod +x deploy-sync.sh
./deploy-sync.sh &
```

This watches your CircleUp folder and automatically:
- Syncs files to Empire server
- Commits changes to GitHub
- Maintains backup

Or manually sync:
```bash
rsync -avz --delete . root@64.227.108.128:/var/www/html/CircleUp/
```

---

## 🔐 Credentials

### Database
```
Host: localhost
User: mcallpl
Pass: REDACTED
DB: circleup
```

### Stripe
All keys in `config.php` (not committed to GitHub)
- **Secret Key:** sk_live_51RfnHU2K...
- **Publishable Key:** pk_live_51RfnHU2K...
- **Webhook Secret:** whsec_kCuJTBZC...

### Admin
```
Username: mcallpl
Password: [in vault - PlayPBNow folder]
```

---

## 🛠️ Common Tasks

### Add a Product
```
Admin Panel → Products → Add Product
Fill form → Upload image → Add variants → Save
```

### Manage Orders
```
Admin Panel → Dashboard → Recent Orders
Click order → Update status (pending → completed → shipped)
```

### Check Sales
```
Admin Panel → Dashboard
View: Total Revenue, Orders, Pending Orders
```

### Test Checkout
```
1. Add product to store
2. Visit /CircleUp/ in browser
3. Click product
4. Add to cart
5. Checkout with test card (4242 4242 4242 4242)
```

### View Orders in Database
```bash
mysql -u mcallpl -p'REDACTED' circleup
SELECT o.order_number, o.customer_name, o.total_amount, o.status 
FROM orders o 
ORDER BY o.created_at DESC;
```

---

## 🐛 Troubleshooting

### Admin login fails
```bash
# Check admin exists
mysql -u mcallpl -p'REDACTED' circleup
SELECT * FROM admins;

# Reset password if needed
UPDATE admins SET password_hash = '$2y$12$hash...' WHERE username='mcallpl';
```

### Products not showing
```bash
# Check products in DB
SELECT COUNT(*) FROM products;

# Check image path
SELECT image_url FROM products LIMIT 1;

# Verify uploads folder exists
ls -la /var/www/html/CircleUp/uploads/
```

### Stripe checkout errors
```bash
# Check webhook logs
tail -f /var/log/apache2/error.log

# Test webhook manually
curl -X POST https://www.peoplestar.com/CircleUp/api/webhook.php

# Verify Stripe keys in config.php
grep STRIPE_SECRET /var/www/html/CircleUp/config.php
```

### Permissions issues
```bash
sudo chown -R www-data:www-data /var/www/html/CircleUp
sudo chmod -R 755 /var/www/html/CircleUp
sudo chmod -R 775 /var/www/html/CircleUp/uploads
```

---

## 📚 Documentation

- **[CIRCLEUP-GUIDE.md](CIRCLEUP-GUIDE.md)** — Complete technical guide
- **[SETUP-CHECKLIST.md](SETUP-CHECKLIST.md)** — Quick setup walkthrough
- **Stripe Docs:** https://stripe.com/docs
- **GitHub Repo:** https://github.com/chipmcallister/circleup

---

## 🎯 Product Categories

- T-Shirts
- Caps
- Sweatshirts
- Pants
- Shoes
- Hoodies
- Accessories

---

## 📊 Product Sizes

XS, S, M, L, XL, 2XL, 3XL, One Size

---

## 🎨 Colors

Black, White, Navy, Gray, Red, Blue, Green, Yellow, Purple, Pink, Orange, Brown

---

## 💡 Tips

1. **Images:** Use 1000x1000px for best quality. WebP format is smaller & faster.
2. **Descriptions:** Write compelling copy that highlights features & materials.
3. **Pricing:** Consider costs, competitor pricing, and margin targets.
4. **Variants:** Create separate variants for different sizes/colors (allows inventory per combo).
5. **Orders:** Check dashboard daily for new orders and update status.
6. **Backups:** GitHub auto-stores all changes — review commits regularly.

---

## 🚀 Next Steps

1. ✅ Add products with images & variants
2. ✅ Test checkout with test card
3. ✅ Set up email notifications (optional)
4. ✅ Monitor Stripe dashboard
5. ✅ Ship orders & update status
6. ✅ Analyze sales & refine offerings

---

## 📞 Support

- **Server SSH:** `ssh root@64.227.108.128`
- **Database:** `mysql -u mcallpl -p'REDACTED' circleup`
- **Logs:** `/var/log/apache2/error.log`
- **Stripe Support:** https://support.stripe.com/

---

**Version:** 1.0.0  
**Built:** 2026-03-31  
**Status:** ✅ LIVE & OPERATIONAL  
**Owner:** Chip McAllister | PeopleStar Enterprises
