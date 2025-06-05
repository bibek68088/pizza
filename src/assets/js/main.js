let cart = JSON.parse(localStorage.getItem("cart")) || [];
let selectedStore = JSON.parse(localStorage.getItem("selectedStore")) || null;
const displayCart = null;

document.addEventListener("DOMContentLoaded", () => {
  updateCartCount();
  initializeEventListeners();
  initializeLocationSelector();
  initializeForms();
});

function updateCartCount() {
  const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
  const cartCountElements = document.querySelectorAll(".cart-count");

  cartCountElements.forEach((element) => {
    element.textContent = cartCount;
    element.style.display = cartCount > 0 ? "inline" : "none";
  });
}

function addToCart(pizzaId, size = "medium", quantity = 1) {
  if (!isLoggedIn()) {
    alert("Please log in to add items to cart");
    window.location.href = "login.php";
    return;
  }

  const existingItem = cart.find(
    (item) => item.pizza_id == pizzaId && item.size === size
  );

  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({
      pizza_id: pizzaId,
      size: size,
      quantity: quantity,
      item_type: "pizza",
    });
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
  showNotification("Item added to cart!", "success");
}

function removeFromCart(index) {
  cart.splice(index, 1);
  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();

  if (window.location.pathname.includes("cart.php")) {
    location.reload();
  }
}

function updateCartQuantity(index, quantity) {
  if (quantity <= 0) {
    removeFromCart(index);
    return;
  }

  cart[index].quantity = quantity;
  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
}

function clearCart() {
  cart = [];
  localStorage.removeItem("cart");
  updateCartCount();

  if (window.location.pathname.includes("cart.php")) {
    location.reload();
  }
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
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
      const index = this.dataset.index;
      updateCartQuantity(index, Number.parseInt(this.value));
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
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

function formatCurrency(amount) {
  return `$${Number.parseFloat(amount).toFixed(2)}`;
}

function calculateOrderTotal(items) {
  const subtotal = items.reduce((total, item) => {
    return total + item.price * item.quantity;
  }, 0);

  const tax = subtotal * 0.1;
  const deliveryFee = 5.5;
  const total = subtotal + tax + deliveryFee;

  return {
    subtotal: subtotal,
    tax: tax,
    deliveryFee: deliveryFee,
    total: total,
  };
}

function trackOrder(orderNumber) {
  if (!orderNumber) {
    alert("Please enter an order number");
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
        ingredientTotal += Number.parseFloat(checkbox.dataset.price || 0);
      }
    });

    const total = basePrice + ingredientTotal;
    priceDisplay.textContent = formatCurrency(total);
  }

  if (sizeSelector) {
    sizeSelector.addEventListener("change", updatePrice);
  }

  ingredientCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updatePrice);
  });

  updatePrice();
}

if (window.location.pathname.includes("build-pizza.php")) {
  document.addEventListener("DOMContentLoaded", initializePizzaBuilder);
}

function initializeLocationSelector() {
  const locationInput = document.getElementById("locationInput");
  if (locationInput) {
    locationInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
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
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault();
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
  const isRequired = field.hasAttribute("required");
  let isValid = true;
  let errorMessage = "";

  field.classList.remove("error");
  const existingError = field.parentNode.querySelector(".error-message");
  if (existingError) {
    existingError.remove();
  }

  if (isRequired && !value) {
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
      showAlert("An error occurred. Please try again.", "error");
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
  debounce,
  scrollToElement,
  toggleMobileMenu,
};
