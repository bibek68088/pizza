let cartItems = [];
let selectedStore = JSON.parse(localStorage.getItem("selectedStore")) || null;

document.addEventListener("DOMContentLoaded", () => {
  updateCartCount();
  initializeEventListeners();
  initializeLocationSelector();
  initializeForms();
  if (window.location.pathname.includes("cart.php")) {
    loadCartItems();
  }
});

document.addEventListener('DOMContentLoaded', function() {
  if (!window.location.pathname.includes('pizza-details.php')) {
      const observerOptions = {
          threshold: 0.1,
          rootMargin: '0px 0px -50px 0px'
      };
      const observer = new IntersectionObserver(function(entries) {
          entries.forEach(entry => {
              if (entry.isIntersecting) {
                  entry.target.style.opacity = '1';
                  entry.target.style.transform = 'translateY(0)';
              }
          });
      }, observerOptions);
      document.querySelectorAll('.fade-in-up').forEach(el => {
          observer.observe(el);
      });
  }
  updateCartCount();
});

function updateCartCount() {
  try {
      let cartCount = 0;
      if (isLoggedIn()) {
          ajax("api/cart_api.php?action=get", { method: "GET" })
              .then((response) => {
                  if (response.success) {
                      cartCount = response.data.items.reduce(
                          (total, item) => total + parseInt(item.quantity),
                          0
                      );
                      updateCartCountDisplay(cartCount);
                  }
              })
              .catch((error) => {
                  console.error("Error updating cart count:", error);
                  updateCartCountDisplay(0);
              });
      } else {
          const localCart = JSON.parse(localStorage.getItem("cart") || "[]");
          cartCount = localCart.reduce(
              (total, item) => total + (item.quantity || 1),
              0
          );
          updateCartCountDisplay(cartCount);
      }
  } catch (error) {
      console.error("Error in updateCartCount:", error);
      updateCartCountDisplay(0);
  }
}

function updateCartCountDisplay(cartCount) {
  const cartCountElements = document.querySelectorAll(".cart-count");
  cartCountElements.forEach((element) => {
      element.textContent = cartCount;
      element.style.display = cartCount > 0 ? "inline-block" : "none";
  });
}

function addToCart(
  pizzaId,
  size = "medium",
  quantity = 1,
  name = "Custom Pizza",
  price = 10.0,
  customIngredients = [],
  specialInstructions = ""
) {
  if (!isLoggedIn()) {
    let localCart = JSON.parse(localStorage.getItem("cart") || "[]");
    const existingItem = localCart.find(
      (item) =>
        item.pizza_id === pizzaId &&
        item.size === size &&
        JSON.stringify(item.customIngredients) ===
          JSON.stringify(customIngredients)
    );
    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      localCart.push({
        pizza_id: pizzaId,
        size,
        quantity,
        item_type: "pizza",
        name,
        price,
        customIngredients,
        specialInstructions,
      });
    }
    localStorage.setItem("cart", JSON.stringify(localCart));
    updateCartCount();
    showNotification(
      "Item added to cart locally! Please log in to save.",
      "warning"
    );
    return;
  }

  const item = {
    pizza_id: pizzaId,
    size,
    quantity,
    item_type: "pizza",
    name,
    price,
    custom_ingredients: JSON.stringify(customIngredients), // Ensure JSON string
    special_instructions: specialInstructions,
    user_id: getUserId(), // Add user_id explicitly
    csrf_token: getCSRFToken(), // Add CSRF token for security
  };

  ajax("api/cart_api.php?action=add", {
    method: "POST",
    body: JSON.stringify(item),
  })
    .then((response) => {
      if (response.success) {
        cartItems = response.data.items;
        updateCartCount();
        showNotification("Item added to cart!", "success");
        if (window.location.pathname.includes("cart.php")) {
          loadCartItems();
        }
      } else {
        showNotification(
          response.message || "Failed to add item to cart",
          "error"
        );
        console.error("Add to cart failed:", response);
      }
    })
    .catch((error) => {
      console.error("Error adding to cart:", error);
      showNotification("An error occurred while adding to cart", "error");
    });
}

// Helper function to get user_id from a global variable or meta tag
function getUserId() {
  const userIdMeta = document.querySelector('meta[name="user-id"]');
  return userIdMeta ? userIdMeta.getAttribute("content") : null;
}

// Helper function to get CSRF token
function getCSRFToken() {
  const tokenInput = document.querySelector('input[name="csrf_token"]');
  return tokenInput ? tokenInput.value : null;
}

function removeFromCart(cartId) {
  ajax("api/cart_api.php?action=remove", {
    method: "POST",
    body: JSON.stringify({ cart_id: cartId }),
  })
    .then((response) => {
      if (response.success) {
        cartItems = response.data.items;
        updateCartCount();
        showNotification("Item removed from cart", "success");
        if (window.location.pathname.includes("cart.php")) {
          loadCartItems();
        }
      } else {
        showNotification("Failed to remove item", "error");
      }
    })
    .catch((error) => {
      console.error("Error removing from cart:", error);
      showNotification("An error occurred", "error");
    });
}

function updateCartQuantity(cartId, quantity) {
  if (quantity <= 0) {
    removeFromCart(cartId);
    return;
  }

  ajax("api/cart_api.php?action=update", {
    method: "POST",
    body: JSON.stringify({ cart_id: cartId, quantity }),
  })
    .then((response) => {
      if (response.success) {
        cartItems = response.data.items;
        updateCartCount();
        showNotification("Quantity updated", "success");
        if (window.location.pathname.includes("cart.php")) {
          loadCartItems();
        }
      } else {
        showNotification("Failed to update quantity", "error");
      }
    })
    .catch((error) => {
      console.error("Error updating quantity:", error);
      showNotification("An error occurred", "error");
    });
}

function clearCart() {
  ajax("api/cart_api.php?action=clear", { method: "POST" })
    .then((response) => {
      if (response.success) {
        cartItems = [];
        localStorage.removeItem("cart");
        updateCartCount();
        showNotification("Cart cleared", "success");
        if (window.location.pathname.includes("cart.php")) {
          loadCartItems();
        }
      } else {
        showNotification("Failed to clear cart", "error");
      }
    })
    .catch((error) => {
      console.error("Error clearing cart:", error);
      showNotification("An error occurred", "error");
    });
}

function syncCartOnLogin() {
  const localCart = JSON.parse(localStorage.getItem("cart") || "[]");
  if (localCart.length > 0) {
    ajax("api/cart_api.php?action=sync", {
      method: "POST",
      body: JSON.stringify({ items: localCart }),
    })
      .then((response) => {
        if (response.success) {
          cartItems = response.data.items;
          localStorage.removeItem("cart");
          updateCartCount();
          showNotification("Cart synced with your account", "success");
          if (window.location.pathname.includes("cart.php")) {
            loadCartItems();
          }
        } else {
          showNotification("Failed to sync cart", "error");
        }
      })
      .catch((error) => {
        console.error("Error syncing cart:", error);
        showNotification("An error occurred", "error");
      });
  }
}

function loadCartItems() {
  ajax("api/cart_api.php?action=get", { method: "GET" })
    .then((response) => {
      if (response.success) {
        cartItems = response.data.items;
        const cartItemsContainer = document.getElementById("cartItems");
        const cartContainer = document.getElementById("cartContainer");
        const emptyCartMessage = document.getElementById("emptyCartMessage");

        if (cartItems.length === 0) {
          cartContainer.style.display = "none";
          emptyCartMessage.style.display = "flex";
          return;
        }

        cartContainer.style.display = "grid";
        emptyCartMessage.style.display = "none";

        let cartHTML = "";
        cartItems.forEach((item) => {
          const customIngredients = item.custom_ingredients
            ? JSON.parse(item.custom_ingredients)
            : [];
          cartHTML += `
            <div class="cart-item">
              <div class="cart-item-info">
                <h4>${item.item_name}</h4>
                ${
                  item.size
                    ? `<p class="item-detail">Size: ${
                        item.size.charAt(0).toUpperCase() + item.size.slice(1)
                      }</p>`
                    : ""
                }
                ${
                  customIngredients.length
                    ? `<p class="item-detail">Ingredients: ${customIngredients.join(
                        ", "
                      )}</p>`
                    : ""
                }
                ${
                  item.special_instructions
                    ? `<p class="item-detail">Instructions: ${item.special_instructions}</p>`
                    : ""
                }
                <p class="item-price">${formatCurrency(item.total_price)}</p>
              </div>
              <div class="cart-item-controls">
                <div class="quantity-controls">
                  <button class="quantity-btn" onclick="updateCartQuantity(${
                    item.cart_id
                  }, ${parseInt(item.quantity) - 1})">
                    <i class="fas fa-minus"></i>
                  </button>
                  <span class="quantity-display">${item.quantity}</span>
                  <button class="quantity-btn" onclick="updateCartQuantity(${
                    item.cart_id
                  }, ${parseInt(item.quantity) + 1})">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${
                  item.cart_id
                })" title="Remove item">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          `;
        });

        cartItemsContainer.innerHTML = cartHTML;
        updateCartSummary();
      } else {
        showNotification("Failed to load cart", "error");
      }
    })
    .catch((error) => {
      console.error("Error loading cart:", error);
      showNotification("An error occurred", "error");
    });
}

function updateCartSummary() {
  const subtotal = cartItems.reduce(
    (total, item) => total + parseFloat(item.total_price),
    0
  );
  const tax = subtotal * 0.1;
  const deliveryFee = subtotal > 30 ? 0 : 5.99;
  const total = subtotal + tax + deliveryFee;

  document.getElementById("cartSubtotal").textContent =
    formatCurrency(subtotal);
  document.getElementById("cartTax").textContent = formatCurrency(tax);
  document.getElementById("deliveryFee").textContent =
    formatCurrency(deliveryFee);
  document.getElementById("cartTotal").textContent = formatCurrency(total);

  const checkoutBtn = document.getElementById("checkoutBtn");
  if (cartItems.length === 0) {
    checkoutBtn.classList.add("disabled");
    checkoutBtn.style.pointerEvents = "none";
  } else {
    checkoutBtn.classList.remove("disabled");
    checkoutBtn.style.pointerEvents = "auto";
  }
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <span>${message}</span>
    <button onclick="this.parentElement.remove()">×</button>
  `;
  document.body.appendChild(notification);
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove();
    }
  }, 5000);
}

function isLoggedIn() {
  return !document.querySelector('a[href="login.php"]');
}

function initializeEventListeners() {
  const sizeSelectors = document.querySelectorAll(".size-selector");
  sizeSelectors.forEach((selector) => {
    selector.addEventListener("change", function () {
      updatePizzaPrice(this);
    });
  });

  const quantityInputs = document.querySelectorAll(".quantity-input");
  quantityInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const cartId = this.dataset.cartId;
      updateCartQuantity(cartId, Number.parseInt(this.value));
    });
  });

  const searchInput = document.querySelector("#search-input");
  if (searchInput) {
    searchInput.addEventListener("input", debounce(handleSearch, 300));
  }

  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", validateForm);
  });
}

function updatePizzaPrice(sizeSelector) {
  const pizzaCard = sizeSelector.closest(".pizza-card");
  const priceDisplay = pizzaCard.querySelector(".current-price");
  const size = sizeSelector.value;

  const prices = {
    small: Number.parseFloat(pizzaCard.dataset.priceSmall),
    medium: Number.parseFloat(pizzaCard.dataset.priceMedium),
    large: Number.parseFloat(pizzaCard.dataset.priceLarge),
  };

  if (priceDisplay && prices[size]) {
    priceDisplay.textContent = `$${prices[size].toFixed(2)}`;
  }
}

function handleSearch(event) {
  const searchTerm = event.target.value.toLowerCase();
  const pizzaCards = document.querySelectorAll(".pizza-card");

  pizzaCards.forEach((card) => {
    const pizzaName = card.querySelector("h3").textContent.toLowerCase();
    const pizzaDescription = card.querySelector("p").textContent.toLowerCase();

    if (
      pizzaName.includes(searchTerm) ||
      pizzaDescription.includes(searchTerm)
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

function validateForm(event) {
  const form = event.target;
  const requiredFields = form.querySelectorAll("[required]");
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      isValid = false;
      field.classList.add("error");
      showFieldError(field, "This field is required");
    } else {
      field.classList.remove("error");
      clearFieldError(field);
    }
  });

  const emailFields = form.querySelectorAll('input[type="email"]');
  emailFields.forEach((field) => {
    if (field.value && !isValidEmail(field.value)) {
      isValid = false;
      field.classList.add("error");
      showFieldError(field, "Please enter a valid email address");
    }
  });

  const phoneFields = form.querySelectorAll('input[type="tel"]');
  phoneFields.forEach((field) => {
    if (field.value && !isValidPhone(field.value)) {
      isValid = false;
      field.classList.add("error");
      showFieldError(field, "Please enter a valid phone number");
    }
  });

  if (!isValid) {
    event.preventDefault();
  }
}

function showFieldError(field, message) {
  clearFieldError(field);
  const errorDiv = document.createElement("div");
  errorDiv.className = "field-error";
  errorDiv.textContent = message;
  field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
  const existingError = field.parentNode.querySelector(".field-error");
  if (existingError) {
    existingError.remove();
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function isValidPhone(phone) {
  const phoneRegex = /^(\+61|0)[2-9]\d{8}$/;
  return phoneRegex.test(phone.replace(/\s/g, ""));
}

function showAlert(message, type = "info") {
  const existingAlerts = document.querySelectorAll(".alert");
  existingAlerts.forEach((alert) => alert.remove());

  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;

  const main = document.querySelector("main") || document.body;
  main.insertBefore(alertDiv, main.firstChild);
  setTimeout(() => alertDiv.remove(), 5000);
}

function formatCurrency(amount) {
  return `$${parseFloat(amount).toFixed(2)}`;
}

function calculateOrderTotal(items) {
  const subtotal = items.reduce(
    (total, item) => total + parseFloat(item.total_price),
    0
  );
  const tax = subtotal * 0.1;
  const deliveryFee = subtotal > 30 ? 0 : 5.99;
  return {
    subtotal,
    tax,
    deliveryFee,
    total: subtotal + tax + deliveryFee,
  };
}

function trackOrder(orderNumber) {
  if (!orderNumber) {
    showAlert("Please enter an order number", "warning");
    return;
  }

  window.location.href = `track-order.php?order=${encodeURIComponent(
    orderNumber
  )}`;
}

function initializePizzaBuilder() {
  const ingredientCheckboxes = document.querySelectorAll(
    ".ingredient-checkbox"
  );
  const sizeSelector = document.querySelector("#pizza-size");
  const priceDisplay = document.querySelector("#total-price");

  function updatePrice() {
    let basePrice = 0;
    const size = sizeSelector.value;

    switch (size) {
      case "small":
        basePrice = 15.9;
        break;
      case "medium":
        basePrice = 21.9;
        break;
      case "large":
        basePrice = 26.9;
        break;
    }

    let ingredientTotal = 0;
    ingredientCheckboxes.forEach((checkbox) => {
      if (checkbox.checked) {
        ingredientTotal += parseFloat(checkbox.dataset.price || 0);
      }
    });

    const totalPrice = basePrice + ingredientTotal;
    priceDisplay.textContent = formatCurrency(totalPrice);
  }

  if (sizeSelector) {
    sizeSelector.addEventListener("change", updatePrice);
  }

  ingredientCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updatePrice);
  });

  updatePrice();
}

if (window.location.pathname.includes("build-pizza")) {
  document.addEventListener("DOMContentLoaded", initializePizzaBuilder);
}

function initializeLocationSelector() {
  const locationInput = document.getElementById("locationInput");
  if (locationInput) {
    locationInput.addEventListener("keypress", (event) => {
      if (event.key === "Enter") {
        findStores();
      }
    });
  }
}

function findStores() {
  const locationInput = document.getElementById("locationInput");
  const storeList = document.getElementById("storeList");

  if (!locationInput || !storeList) return;

  const location = locationInput.value.trim();
  if (!location) {
    showAlert("Please enter a location", "warning");
    return;
  }

  storeList.innerHTML = '<div class="spinner"></div>';

  setTimeout(() => {
    const stores = [
      {
        id: 1,
        name: "Crust Pizza Annandale",
        address: "123 Parramatta Rd, Annandale NSW 2038",
        phone: "(02) 9560 1234",
        distance: "2.5 km",
      },
      {
        id: 2,
        name: "Crust Pizza Richmond",
        address: "456 Swan St, Richmond VIC 3121",
        phone: "(03) 9428 5678",
        distance: "5.2 km",
      },
    ];

    displayStores(stores);
  }, 1000);
}

function displayStores(stores) {
  const storeList = document.getElementById("storeList");
  if (!storeList) return;

  if (stores.length === 0) {
    storeList.innerHTML = "<p>No stores found in your area.</p>";
    return;
  }

  const storeHTML = stores
    .map(
      (store) => `
      <div class="store-card">
        <h4>${store.name}</h4>
        <p><i class="fas fa-map-marker-alt"></i> ${store.address}</p>
        <p><i class="fas fa-phone"></i> ${store.phone}</p>
        <p><i class="fas fa-route"></i> ${store.distance} away</p>
        <button class="btn btn-primary" onclick="selectStore(${store.id}, '${store.name}')">
          Select This Store
        </button>
      </div>
    `
    )
    .join("");

  storeList.innerHTML = storeHTML;
}

function selectStore(storeId, storeName) {
  selectedStore = { id: storeId, name: storeName };
  localStorage.setItem("selectedStore", JSON.stringify(selectedStore));
  showAlert(`Selected ${storeName} for your order`, "success");
}

function initializeForms() {
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", function (event) {
      if (!validateForm(event)) {
        event.preventDefault();
      }
    });
  });

  const inputs = document.querySelectorAll(".form-control");
  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      validateField(this);
    });
  });
}

function validateField(field) {
  const value = field.value.trim();
  const fieldType = field.type;
  const required = field.hasAttribute("required");
  let isValid = true;
  let errorMessage = "";

  field.classList.remove("error");
  const existingError = field.parentNode.querySelector(".error-message");
  if (existingError) {
    existingError.remove();
  }

  if (required && !value) {
    isValid = false;
    errorMessage = "This field is required";
  } else if (fieldType === "email" && value && !isValidEmail(value)) {
    isValid = false;
    errorMessage = "Please enter a valid email address";
  } else if (field.name === "phone" && value && !isValidPhone(value)) {
    isValid = false;
    errorMessage = "Please enter a valid Australian phone number";
  } else if (fieldType === "password" && value && value.length < 6) {
    isValid = false;
    errorMessage = "Password must be at least 6 characters long";
  } else if (field.name === "confirm_password") {
    const passwordField = document.querySelector('input[name="password"]');
    if (passwordField && value !== passwordField.value) {
      isValid = false;
      errorMessage = "Passwords do not match";
    }
  }

  if (!isValid) {
    field.classList.add("error");
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message";
    errorDiv.textContent = errorMessage;
    field.parentNode.appendChild(errorDiv);
  }

  return isValid;
}

function ajax(url, options = {}) {
  const defaultOptions = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  };

  const config = { ...defaultOptions, ...options };

  return fetch(url, config)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .catch((error) => {
      console.error("AJAX Error:", error);
      showNotification("An error occurred. Please try again.", "error");
      throw error;
    });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function scrollToElement(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}

function toggleMobileMenu() {
  const navMenu = document.querySelector(".nav-menu");
  if (navMenu) {
    navMenu.classList.toggle("mobile-open");
  }
}

window.CrustPizza = {
  addToCart,
  removeFromCart,
  updateCartQuantity,
  clearCart,
  calculateOrderTotal,
  trackOrder,
  showAlert,
  formatCurrency,
  ajax,
  loadCartItems,
  syncCartOnLogin,
  showNotification,
  debounce,
  scrollToElement,
  toggleMobileMenu,
};
