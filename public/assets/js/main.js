// Main JavaScript file for Online Shop

document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds (except for cart empty message and modal alerts)
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    // Don't auto-hide the empty cart message or alerts inside modals
    if (
      !alert.textContent.includes("Your cart is empty") &&
      !alert.closest(".modal")
    ) {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }, 5000);
    }
  });

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll("[data-confirm]");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Are you sure you want to delete this item?")) {
        e.preventDefault();
      }
    });
  });

  // Image preview for file uploads
  const imageInputs = document.querySelectorAll(
    'input[type="file"][accept*="image"]'
  );
  imageInputs.forEach((input) => {
    input.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          let preview = document.getElementById("imagePreview");
          if (!preview) {
            preview = document.createElement("img");
            preview.id = "imagePreview";
            preview.className = "img-fluid mt-2";
            preview.style.maxWidth = "200px";
            input.parentElement.appendChild(preview);
          }
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // Quantity validation
  const quantityInputs = document.querySelectorAll(
    'input[type="number"][name*="qty"]'
  );
  quantityInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const min = parseInt(this.min) || 1;
      const max = parseInt(this.max) || 999;
      let value = parseInt(this.value);

      if (value < min) {
        this.value = min;
      } else if (value > max) {
        this.value = max;
        alert(`Maximum available quantity is ${max}`);
      }
    });
  });

  // Search form validation
  const searchForms = document.querySelectorAll('form[action*="search"]');
  searchForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const searchInput = this.querySelector('input[name="search"]');
      if (searchInput && searchInput.value.trim() === "") {
        e.preventDefault();
        alert("Please enter a search term");
        searchInput.focus();
      }
    });
  });

  // Form validation
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  // Console log for debugging (remove in production)
  console.log("Online Shop - JavaScript Loaded");
});

// Helper function to format currency
function formatCurrency(amount) {
  return (
    "₱" +
    parseFloat(amount)
      .toFixed(2)
      .replace(/\d(?=(\d{3})+\.)/g, "$&,")
  );
}

// Helper function to update cart count
function updateCartCount() {
  // This can be enhanced with AJAX to dynamically update cart count
  console.log("Cart count update requested");
}
