# CircleUp Setup Checklist ✅

## System Status

- ✅ MySQL Database `circleup` created
- ✅ Admin user `mcallpl` created
- ✅ Stripe keys configured
- ✅ Directory structure built
- ✅ All PHP files deployed

## What's Ready to Use

### Admin Dashboard
- **URL:** https://www.peoplestar.com/CircleUp/admin/login.php
- **Username:** mcallpl
- **Password:** Use vault credentials
- **Features:**
  - ✅ Product upload with images
  - ✅ Variant management (sizes, colors, stock)
  - ✅ Order tracking
  - ✅ Revenue dashboard

### Public Storefront
- **URL:** https://www.peoplestar.com/CircleUp/
- **Features:**
  - ✅ Browse products by category
  - ✅ Search functionality
  - ✅ Responsive design
  - ✅ Stripe checkout integration

## Next Steps

### 1. Add Your First Product
```
1. Go to /CircleUp/admin/login.php
2. Login with mcallpl
3. Click "Products" → "Add Product"
4. Fill in:
   - Name: "CircleUp T-Shirt"
   - Price: $29.99
   - Category: T-Shirts
   - Image: Upload your product photo
5. Add variants (sizes S-XXL, colors)
6. Save
```

### 2. Set Up Auto-Sync from Mac
```bash
# On your Mac:
cd /Users/chipmcallister/Projects/CircleUp
chmod +x deploy-sync.sh
./deploy-sync.sh &   # Run in background
```

This will:
- Watch for file changes in your CircleUp folder
- Automatically sync to Empire every time you save
- Commit changes to GitHub

### 3. Test Stripe Checkout
```
1. Add a product with variants
2. Visit /CircleUp/ in browser
3. Add product to cart
4. Click "Checkout"
5. Use test card: 4242 4242 4242 4242
6. Verify order appears in admin dashboard
```

### 4. Monitor Orders
```
1. Go to admin dashboard
2. View "Recent Orders" 
3. Click any order to see details
4. Update status: pending → completed → shipped
```

## File Structure

```
/var/www/html/CircleUp/
├── admin/
│   ├── login.php           (Admin login page)
│   ├── dashboard.php       (Admin dashboard & stats)
│   ├── product-form.php    (Add/edit products)
│   ├── auth.php            (Authentication system)
│   └── logout.php          (Logout handler)
├── api/
│   ├── checkout.php        (Stripe checkout API)
│   └── webhook.php         (Stripe webhook handler)
├── store/
│   └── index.php           (Public storefront)
├── uploads/                (Product images)
├── vendor/                 (Composer dependencies)
├── config.php              (Database & Stripe config)
├── .htaccess               (URL rewriting)
├── composer.json           (Dependencies)
├── CIRCLEUP-GUIDE.md       (Full documentation)
└── SETUP-CHECKLIST.md      (This file)
```

## Credentials Reference

### Database
- **Host:** localhost
- **User:** mcallpl
- **Password:** REDACTED
- **Database:** circleup

### Stripe
- **Secret:** sk_live_51RfnHU2K... (in config.php)
- **Publishable:** pk_live_51RfnHU2K... (in config.php)
- **Webhook:** /CircleUp/api/webhook.php

### Admin
- **Username:** mcallpl
- **Password:** (See vault file in PlayPBNow)

## Testing URLs

- **Storefront:** https://www.peoplestar.com/CircleUp/
- **Admin Login:** https://www.peoplestar.com/CircleUp/admin/login.php
- **API Checkout:** https://www.peoplestar.com/CircleUp/api/checkout.php
- **Webhook:** https://www.peoplestar.com/CircleUp/api/webhook.php

## Quick Commands

### SSH to Empire
```bash
ssh root@64.227.108.128
cd /var/www/html/CircleUp
```

### View Database
```bash
mysql -u mcallpl -p'REDACTED' circleup
SHOW TABLES;
SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM orders;
```

### Check File Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/CircleUp
sudo chmod -R 755 /var/www/html/CircleUp
sudo chmod -R 775 /var/www/html/CircleUp/uploads
```

### View Error Logs
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

## Performance Notes

- Images cached for 1 year (HTTP cache control)
- Gzip compression enabled for HTML/CSS/JS
- Database queries optimized with indexes
- Stripe SDK lazy-loaded (only for checkout)

## Security Status

- ✅ Passwords bcrypt hashed
- ✅ SQL injection prevention (prepared statements)
- ✅ File upload validation
- ✅ HTTPS enforced (SSL cert on server)
- ✅ Admin session timeout (1 hour)
- ✅ Stripe PCI DSS compliant

## Support Resources

1. **CircleUp Documentation:** See CIRCLEUP-GUIDE.md
2. **Stripe Docs:** https://stripe.com/docs
3. **GitHub Repo:** https://github.com/chipmcallister/circleup
4. **Server Access:** root@64.227.108.128

---

**System Ready:** 2026-03-31 05:31 UTC  
**Status:** ✅ LIVE & OPERATIONAL
