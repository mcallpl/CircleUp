# CircleUp Editor Guide

## Overview

The **Editor** role is a restricted account designed for team members who manage products without having access to orders, payments, or admin settings.

---

## 📋 Editor Capabilities

### ✅ What Editors CAN Do
- **Add Products** — Upload new products with images, prices, and descriptions
- **Edit Products** — Modify existing product details, images, and pricing
- **Delete Products** — Remove products from the catalog
- **Manage Variants** — Create and manage sizes, colors, and stock levels
- **View Products** — See all products and their information
- **View Orders** — See all customer orders and status
- **View Revenue** — Access sales analytics and revenue reports
- **Track Pending Orders** — Monitor orders waiting for processing

### ❌ What Editors CANNOT Do
- Change admin settings or configurations
- Create or manage admin/editor accounts
- View payment details or Stripe information
- Access audit logs
- Change database structure
- Modify system settings
- Update order status (read-only viewing)

---

## 🔐 Login Credentials

**URL:** https://www.peoplestar.com/CircleUp/admin/login.php

```
Username: Teddy
Password: Trucker
```

---

## 📊 Editor Dashboard

When you log in, you'll see:

### Stats Section
- **Total Products** — Number of products in catalog
- **Total Variants** — Number of size/color combinations
- **Total Orders** — All orders (completed + pending)
- **Total Revenue** — Sum of all completed sales
- **Pending Orders** — Orders waiting for processing

### Content Sections
- **Products Table** — All products with quick edit/delete actions
- **Recent Orders Table** — Last 5 orders with status

---

## ➕ Add a Product

1. Click **"+ Add Product"** button
2. Fill in required fields:
   - **Product Name** ✓ (required)
   - **Price** ✓ (required)
   - **Category** ✓ (required) — Choose from:
     - T-Shirts
     - Caps
     - Sweatshirts
     - Pants
     - Shoes
     - Hoodies
     - Accessories
3. Fill in optional fields:
   - **Description** — Marketing copy
   - **Image** — Product photo (JPG, PNG, WebP, max 5MB)
4. Add **Variants** (optional):
   - **Size** — XS, S, M, L, XL, 2XL, 3XL, One Size
   - **Color** — Choose from 12 colors
   - **Stock** — Number of units available
5. Click **"Create Product"**
6. Product appears on storefront instantly

---

## ✏️ Edit a Product

1. Go to **Products** section
2. Click **"Edit"** on any product
3. Modify:
   - Name, price, description
   - Category
   - Image (upload new or keep existing)
   - Variants (sizes, colors, stock)
4. Click **"Update Product"**
5. Changes live immediately

---

## 🗑️ Delete a Product

1. Go to **Products** section
2. Click **"Delete"** on any product
3. Confirm deletion
4. Product removed from catalog and database

---

## 📦 Managing Variants

**What are variants?**
Variants are different versions of a product (different sizes/colors).

**How to add variants:**
1. When creating/editing a product
2. Scroll to "Variants (Sizes & Colors)" section
3. Click **"+ Add Variant"**
4. Select:
   - Size (e.g., M)
   - Color (e.g., Black)
   - Stock (e.g., 50 units)
5. Repeat for each combination
6. Save product

**Example:**
```
Product: "CircleUp T-Shirt"
Price: $29.99

Variants:
├─ Size M, Color Black, Stock: 50
├─ Size M, Color White, Stock: 40
├─ Size L, Color Black, Stock: 75
├─ Size L, Color White, Stock: 60
└─ Size XL, Color Black, Stock: 55
```

---

## 🎨 Sizes & Colors Available

### Sizes
- XS (Extra Small)
- S (Small)
- M (Medium)
- L (Large)
- XL (Extra Large)
- 2XL (2X Large)
- 3XL (3X Large)
- One Size (fits all)

### Colors
- Black
- White
- Navy
- Gray
- Red
- Blue
- Green
- Yellow
- Purple
- Pink
- Orange
- Brown

---

## 🖼️ Image Upload

### Requirements
- **Max Size:** 5MB
- **Format:** JPG, PNG, or WebP
- **Recommended:** 1000x1000px (square)
- **WebP preferred** — Smaller file size, faster loading

### Tips
1. Use clear, high-quality product photos
2. Show product clearly without busy background
3. Use consistent lighting across products
4. WebP format recommended (better compression)

---

## 🔍 Finding Products

1. Go to **Products** section
2. Products listed in order (newest first)
3. Each product shows:
   - Product name
   - Category
   - Price
   - Creation date
   - Edit/Delete links

---

## 💡 Best Practices

### Product Naming
- Be clear and descriptive
- Include key info: "CircleUp Navy Hoodie"
- Use consistent naming style

### Pricing
- Keep prices competitive
- Consider materials, labor, overhead
- Factor in profit margin

### Descriptions
- Write compelling copy
- Highlight key features (materials, fit, durability)
- Keep it concise (2-3 sentences)

### Variants
- Create variants for all size/color combinations
- Maintain accurate stock counts
- Remove variants if no longer available

### Images
- Upload high-resolution photos
- Use consistent branding
- Show product clearly
- Consider using WebP format

---

## ⚠️ Important Notes

1. **Data Safety** — All product deletions are permanent. Double-check before deleting.
2. **Live Changes** — Products appear on storefront immediately after saving.
3. **Stock Accuracy** — Keep variant stock counts updated for accurate inventory.
4. **Audit Log** — All your edits are logged for compliance and tracking.
5. **No Admin Access** — You cannot change system settings or access orders.

---

## 🆘 Troubleshooting

### Image Upload Fails
- Check file size (max 5MB)
- Verify format (JPG, PNG, or WebP)
- Try a different image

### Product Not Appearing on Storefront
- Verify product was saved
- Check that name, price, and category are filled
- Clear browser cache
- Wait 30 seconds for cache to refresh

### Cannot Edit/Delete a Product
- Verify you're logged in as Editor
- Check that product still exists in database
- Try refreshing page

### Session Expired
- You're automatically logged out after 1 hour of inactivity
- Log back in at: https://www.peoplestar.com/CircleUp/admin/login.php

---

## 📞 Support

- **Admin:** Contact Chip McAllister
- **Technical Issues:** SSH to Empire or check logs
- **Questions:** Refer to CIRCLEUP-GUIDE.md for technical details

---

## 🎯 Daily Tasks

**Morning Checklist:**
1. Log in as Teddy
2. Check Products dashboard
3. Update any stock counts if needed
4. Add new products if available
5. Log out when done

**Quick Actions:**
- Add product: 2 min
- Edit product: 1 min
- Delete product: 30 sec
- Add variant: 1 min

---

**Version:** 1.0.0  
**Role:** Editor  
**User:** Teddy  
**Status:** ✅ Active
