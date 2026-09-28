// Wait for DOM to load
document.addEventListener("DOMContentLoaded", function () {
  // Initialize date group toggles
  initDateToggles();

  // Initialize search functionality
  initSearch();

  // DO NOT add global delete listener - let individual pages handle it
  // This prevents double confirmation dialogs
});

// ========================================
// Date Group Toggle Functionality
// ========================================

function initDateToggles() {
  const dateHeaders = document.querySelectorAll(".date-header");

  dateHeaders.forEach((header) => {
    // Remove any existing event listeners by cloning
    const newHeader = header.cloneNode(true);
    header.parentNode.replaceChild(newHeader, header);

    newHeader.addEventListener("click", function (e) {
      e.stopPropagation();
      toggleDateGroup(this);
    });
  });
}

function toggleDateGroup(element) {
  const content = element.nextElementSibling;
  if (content) {
    content.classList.toggle("hide");
    element.classList.toggle("collapsed");
  }
}

// ========================================
// Search Functionality
// ========================================

function initSearch() {
  const searchInput = document.getElementById("searchInput");
  if (!searchInput) return;

  searchInput.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase().trim();
    const dateGroups = document.querySelectorAll(".date-group");

    dateGroups.forEach((group) => {
      const rows = group.querySelectorAll(".date-content tbody tr");
      let hasVisibleRows = false;

      rows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        if (filter === "" || text.indexOf(filter) > -1) {
          row.style.display = "";
          hasVisibleRows = true;
        } else {
          row.style.display = "none";
        }
      });

      // Hide or show the entire date group based on visible rows
      group.style.display = hasVisibleRows ? "block" : "none";
    });
  });
}

// ========================================
// Global Delete Functions (Single Confirmation)
// ========================================

function deleteCustomer(id) {
  // Only ONE confirmation dialog
  if (
    confirm(
      "Are you sure you want to delete this customer? This action cannot be undone.",
    )
  ) {
    const formData = new FormData();
    formData.append("id", id);

    fetch(BASE_URL + "api/customers.php", {
      method: "DELETE",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "id=" + id,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showToast("Customer deleted successfully", "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast("Error: " + data.message, "error");
        }
      })
      .catch((error) => {
        showToast("Network error. Please try again.", "error");
      });
  }
}

function deleteEmployee(id) {
  // Only ONE confirmation dialog
  if (
    confirm(
      "Are you sure you want to delete this employee? This action cannot be undone.",
    )
  ) {
    fetch(BASE_URL + "api/employees.php", {
      method: "DELETE",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "id=" + id,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showToast("Employee deleted successfully", "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast("Error: " + data.message, "error");
        }
      })
      .catch((error) => {
        showToast("Network error. Please try again.", "error");
      });
  }
}

// ========================================
// Toast Notification
// ========================================

function showToast(message, type = "success") {
  // Remove existing toast
  const existingToast = document.querySelector(".toast-notification");
  if (existingToast) existingToast.remove();

  // Create toast element
  const toast = document.createElement("div");
  toast.className = `toast-notification toast-${type}`;
  toast.innerHTML = `
        <i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i>
        <span>${message}</span>
    `;

  document.body.appendChild(toast);

  // Show toast
  setTimeout(() => toast.classList.add("show"), 10);

  // Auto remove after 3 seconds
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ========================================
// Form Validation Helpers
// ========================================

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function validatePhone(phone) {
  const re = /^[\+\d\s\-\(\)]{10,20}$/;
  return re.test(phone);
}

function showFieldError(element, message) {
  const formGroup = element.closest(".form-group");
  const existingError = formGroup.querySelector(".field-error");
  if (existingError) existingError.remove();

  const errorSpan = document.createElement("span");
  errorSpan.className = "field-error";
  errorSpan.innerHTML = message;
  formGroup.appendChild(errorSpan);
  element.classList.add("error");
}

function clearFieldErrors() {
  document.querySelectorAll(".field-error").forEach((el) => el.remove());
  document
    .querySelectorAll(".form-control.error")
    .forEach((el) => el.classList.remove("error"));
}

// ========================================
// BASE_URL from meta tag
// ========================================

var BASE_URL =
  document.querySelector('meta[name="base-url"]')?.getAttribute("content") ||
  "/crm_system/";
