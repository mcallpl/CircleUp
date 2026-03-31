// Shopping Cart Management
class ShoppingCart {
    constructor() {
        this.cartKey = 'circleup_cart';
        this.cart = this.loadCart();
    }

    loadCart() {
        const saved = localStorage.getItem(this.cartKey);
        return saved ? JSON.parse(saved) : [];
    }

    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    }

    addProduct(product) {
        const existing = this.cart.find(item => item.id === product.id);
        
        if (existing) {
            existing.quantity += 1;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                category: product.category,
                image: product.image,
                quantity: 1
            });
        }
        
        this.saveCart();
        this.updateCartUI();
        return true;
    }

    removeProduct(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCartUI();
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(i => i.id === productId);
        if (item) {
            item.quantity = Math.max(1, quantity);
            this.saveCart();
            this.updateCartUI();
        }
    }

    getTotal() {
        return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    getItemCount() {
        return this.cart.reduce((sum, item) => sum + item.quantity, 0);
    }

    updateCartUI() {
        const count = this.getItemCount();
        const badge = document.querySelector('.cart-badge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    clear() {
        this.cart = [];
        this.saveCart();
        this.updateCartUI();
    }
}

// Initialize cart
const cart = new ShoppingCart();

// Add to cart functionality
function addToCart(productId, productName, productPrice, productCategory, productImage) {
    cart.addProduct({
        id: productId,
        name: productName,
        price: productPrice,
        category: productCategory,
        image: productImage
    });
    
    showNotification(`${productName} added to cart!`);
}

// Notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 3000);
}

// Initialize cart UI on page load
document.addEventListener('DOMContentLoaded', () => {
    cart.updateCartUI();
});
