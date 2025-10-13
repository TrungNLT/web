// js/cart.js

/**
 * Hiển thị một thông báo nhỏ ở góc màn hình.
 * @param {string} message Nội dung thông báo.
 * @param {string} type Loại thông báo ('success' hoặc 'error').
 */
function notify(message, type = "success") {
    const div = document.createElement("div");
    div.className = `toast ${type}`;
    div.innerText = message;
    Object.assign(div.style, {
        position: "fixed",
        bottom: "20px",
        right: "20px",
        background: type === "error" ? "#e74c3c" : "#2ecc71",
        color: "white",
        padding: "12px 20px",
        borderRadius: "5px",
        zIndex: 9999,
        fontSize: "15px",
        boxShadow: "0 4px 8px rgba(0,0,0,0.2)",
        opacity: 0,
        transition: "opacity 0.3s, transform 0.3s",
        transform: "translateY(20px)"
    });
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity = 1; div.style.transform = "translateY(0)"; }, 10);
    setTimeout(() => { div.style.opacity = 0; div.style.transform = "translateY(20px)"; setTimeout(() => div.remove(), 300); }, 3000);
}

// Helpers
const getCurrentUser = () => JSON.parse(localStorage.getItem('user') || 'null');
const cartStorageKey = (u = getCurrentUser()) => (u ? `cart_${u.id}` : 'cart_guest');
const ordersStorageKey = (u = getCurrentUser()) => (u ? `orders_${u.id}` : null);

// Di trú từ khoá cũ 'cart' -> đúng key (guest hoặc user)
(function migrateLegacyCartKey(){
    try {
        const legacy = localStorage.getItem('cart');
        if (!legacy) return;
        const arr = JSON.parse(legacy || '[]');
        if (!Array.isArray(arr) || !arr.length) { localStorage.removeItem('cart'); return; }
        const key = cartStorageKey();
        const curr = JSON.parse(localStorage.getItem(key) || '[]');
        // Hợp nhất theo productId/id
        const map = new Map();
        [...curr, ...arr].forEach(it => {
            const id = Number(it.productId ?? it.ProductId ?? it.id);
            const qty = Number(it.qty ?? it.quantity ?? 1);
            if (!map.has(id)) map.set(id, { productId: id, qty: 0 });
            map.get(id).qty += qty;
        });
        localStorage.setItem(key, JSON.stringify([...map.values()]));
        localStorage.removeItem('cart');
    } catch { /* noop */ }
})();

// Cart API (duy nhất) — luôn lưu theo từng user (hoặc guest)
const Cart = {
    async getProductById(id) {
        try {
            const response = await fetch("database/product.json");
            if (!response.ok) throw new Error('Không thể tải file sản phẩm.');
            const products = await response.json();
            return products.find(p => Number(p.ProductId) === Number(id)) || null;
        } catch (error) {
            console.error("Lỗi getProductById:", error);
            return null;
        }
    },

    // Lấy/Lưu giỏ cho user hiện tại
    getCart() {
        try { return JSON.parse(localStorage.getItem(cartStorageKey()) || '[]'); } catch { return []; }
    },
    setCart(items) {
        localStorage.setItem(cartStorageKey(), JSON.stringify(items || []));
        window.dispatchEvent(new CustomEvent('cartUpdated'));
    },

    // Thêm sản phẩm
    async add(id, quantity = 1) {
        const user = getCurrentUser();
        if (!user) { notify('Vui lòng đăng nhập để thêm sản phẩm!', 'error'); return false; }
        const product = await this.getProductById(id);
        if (!product) { notify('Lỗi: Sản phẩm không tồn tại.', 'error'); return false; }
        const qty = Number(quantity) || 1;
        const maxStock = Number(product.Avaiable_quantity ?? product.Available_quantity ?? product.Quantity ?? 0) || 0;
        if (maxStock <= 0) { notify(`'${product.Name}' hiện đã hết hàng.`, 'error'); return false; }
        const cart = this.getCart();
        const pid = Number(product.ProductId);
        const i = cart.findIndex(x => Number(x.productId ?? x.id) === pid);
        const currentQty = i >= 0 ? Number(cart[i].qty ?? cart[i].quantity ?? 0) : 0;
        // Kiểm tra tổng sau khi cộng
        if (currentQty >= maxStock) {
            notify(`Bạn đã đạt số lượng tối đa (${maxStock}) của '${product.Name}'.`, 'error');
            return false;
        }
        const desiredTotal = currentQty + qty;

        // Nếu vượt quá tồn tối đa: chỉ cảnh báo, KHÔNG thay đổi giỏ
        if (desiredTotal > maxStock) {
            const remain = Math.max(0, maxStock - currentQty);
            notify(
                remain > 0
                    ? `Chỉ thêm được ${remain} (tối đa ${maxStock}).`
                    : `Đã đạt tối đa ${maxStock}.`,
                'error'
            );
            return false; // <- quan trọng: không cộng thêm
        }

        if (i >= 0) cart[i].qty = desiredTotal; else cart.push({ productId: pid, qty });
        this.setCart(cart);
  notify(`Đã thêm '${product.Name}' vào giỏ hàng!`);
        return true;
    },

    // Cập nhật số lượng
    async update(id, quantity) {
        let qty = Number(quantity);
        if (!qty || qty <= 0) { this.delete(id); return; }
        const product = await this.getProductById(id);
        if (!product) { notify('Lỗi: Sản phẩm không tồn tại.', 'error'); return; }
        const max = Number(product.Avaiable_quantity ?? product.Available_quantity ?? product.Quantity ?? 0);
        if (qty > max) { notify(`'${product.Name}' chỉ còn ${max} sản phẩm.`, 'error'); window.dispatchEvent(new CustomEvent('cartUpdated')); return; }
        const cart = this.getCart().map(x => {
            if (Number(x.productId ?? x.id) === Number(id)) return { ...x, qty };
            return x;
        });
        this.setCart(cart);
    },

    // Xoá 1 sản phẩm
    delete(id) {
        const cart = this.getCart().filter(x => Number(x.productId ?? x.id) !== Number(id));
        this.setCart(cart);
    },
    removeFromCart(id) { this.delete(id); },

    // Xoá sạch giỏ
    clear() { this.setCart([]); },

    // Đặt hàng theo user hiện tại
    placeOrder(orderInfo = {}) {
        const user = getCurrentUser();
        if (!user) throw new Error('Vui lòng đăng nhập để đặt hàng');
        const items = this.getCart();
        if (!items.length) throw new Error('Giỏ hàng trống');
        const key = ordersStorageKey(user);
        const orders = JSON.parse(localStorage.getItem(key) || '[]');
        const order = { id: Date.now(), date: new Date().toISOString(), items, ...orderInfo };
        orders.push(order);
        localStorage.setItem(key, JSON.stringify(orders));
        this.clear();
        return order;
    },

    getOrders() {
        const user = getCurrentUser();
        if (!user) return [];
        try { return JSON.parse(localStorage.getItem(ordersStorageKey(user)) || '[]'); } catch { return []; }
    }
};

// Small notifier helper (uses your toast if available, falls back to alert)
(function () {
  if (window.Cart && window.Cart.__patchedAddGuard) return;

  const notify = (msg, type) => {
    try {
      if (window.showToast) return window.showToast(msg, type || "error");
      if (window.toastr) return window.toastr[type || "error"](msg);
    } catch (_) {}
    alert(msg);
  };

  const getCartArr = () => (Cart.getCart ? Cart.getCart() : (JSON.parse(localStorage.getItem("cart") || "[]")));
  const saveCartArr = (arr) => {
    if (Cart.saveCart) Cart.saveCart(arr);
    else localStorage.setItem("cart", JSON.stringify(arr));
  };

  // Guarded add: reject if exceed max (do NOT change cart)
  const guardedAdd = function (productId, addQty = 1, opts = {}) {
    const maxQty = Number(opts.maxQty ?? opts.available ?? opts.stock ?? Infinity);
    const cart = getCartArr();
    const idx = cart.findIndex(
      (it) => String(it.productId ?? it.ProductId) === String(productId)
    );
    const current = idx > -1 ? Number(cart[idx].qty ?? cart[idx].quantity ?? 0) : 0;
    const requested = Number(addQty || 0);

    if (!Number.isFinite(requested) || requested <= 0) {
      return { ok: false, added: 0, reason: "invalid-qty" };
    }

    if (Number.isFinite(maxQty) && current + requested > maxQty) {
      const remain = Math.max(0, maxQty - current);
      notify(
        remain > 0
          ? `Chỉ thêm được ${remain} (tối đa ${maxQty}).`
          : `Đã đạt tối đa ${maxQty}.`,
        "error"
      );
      // DO NOT modify cart if exceeding
      return { ok: false, added: 0, reason: "exceed-max", remain, maxQty, current };
    }

    // Proceed to add
    if (idx > -1) {
      cart[idx].qty = current + requested;
    } else {
      cart.push({ productId, qty: requested });
    }
    saveCartArr(cart);
    window.dispatchEvent(new Event("cartUpdated"));
    return { ok: true, added: requested, qty: current + requested };
  };

  // Expose guarded add method while keeping old API
  const originalAdd = Cart.add;
  Cart.add = function (productId, addQty = 1, opts = {}) {
    // If caller provides a max/available hint, use guarded flow
    if (opts && (opts.maxQty != null || opts.available != null || opts.stock != null)) {
      return guardedAdd(productId, addQty, opts);
    }
    // Fallback: keep original behavior if no stock info is provided
    if (typeof originalAdd === "function") {
      return originalAdd.call(Cart, productId, addQty, opts);
    }
    // No original: still guard without a limit
    return guardedAdd(productId, addQty, opts);
  };
  Cart.__patchedAddGuard = true;

  // Global helper usable from HTML: handleAddToCart(pid, maxQty?, qty?)
  window.handleAddToCart = function (pid, maxQty, qty) {
    let addQty = qty;
    // Try to detect qty from page if not provided (product_detail, etc.)
    if (addQty == null) {
      const input =
        document.querySelector(".pro-qty input") ||
        document.querySelector("#quantity") ||
        document.querySelector('input[name="quantity"]');
      addQty = Number(input ? input.value : 1);
    }
    addQty = Number(addQty || 1);

    const res = Cart.add(pid, addQty, { maxQty });
    if (res && res.ok) {
      try {
        if (window.showToast) window.showToast("Đã thêm vào giỏ hàng.", "success");
      } catch (_) {}
    }
    return res?.ok === true;
  };
})();

// Xoá cờ redirect cũ nếu có
document.addEventListener('DOMContentLoaded', () => {
    localStorage.removeItem('redirectToCart');
    sessionStorage.removeItem('redirectToCart');
});

// Gắn Cart vào window để các trang khác có thể truy cập window.Cart
try { window.Cart = Object.assign({}, Cart); } catch {}


