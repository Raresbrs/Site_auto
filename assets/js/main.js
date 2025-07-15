/**
 * AUTO RARES - Main JavaScript
 * Funcționalități principale pentru site
 */

// Configurație globală
const AutoRares = {
  config: {
    animationDuration: 300,
    scrollTopOffset: 100,
    imageLoadDelay: 100,
    searchDelay: 500,
  },

  // Inițializare
  init() {
    this.setupEventListeners();
    this.initializeComponents();
    this.handlePageLoad();
  },

  // Event listeners
  setupEventListeners() {
    // Back to top button
    this.initBackToTop();

    // Form validations
    this.initFormValidations();

    // Image galleries
    this.initImageGalleries();

    // Search functionality
    this.initSearchFunctionality();

    // Tooltips and popovers
    this.initBootstrapComponents();

    // Loading states
    this.initLoadingStates();

    // Accessibility improvements
    this.initAccessibility();
  },

  // Inițializează componentele
  initializeComponents() {
    // Lazy loading pentru imagini
    this.initLazyLoading();

    // Auto-hide alerts
    this.initAutoHideAlerts();

    // Smooth scrolling
    this.initSmoothScrolling();

    // Form enhancements
    this.initFormEnhancements();
  },

  // Back to top functionality
  initBackToTop() {
    const backToTopBtn = document.getElementById("backToTop");
    if (!backToTopBtn) return;

    // Show/hide pe scroll
    window.addEventListener("scroll", () => {
      if (window.pageYOffset > this.config.scrollTopOffset) {
        backToTopBtn.style.display = "block";
        setTimeout(() => backToTopBtn.classList.add("show"), 10);
      } else {
        backToTopBtn.classList.remove("show");
        setTimeout(() => {
          if (!backToTopBtn.classList.contains("show")) {
            backToTopBtn.style.display = "none";
          }
        }, this.config.animationDuration);
      }
    });

    // Click handler
    backToTopBtn.addEventListener("click", (e) => {
      e.preventDefault();
      this.scrollToTop();
    });
  },

  // Scroll to top smooth
  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  },

  // Form validations
  initFormValidations() {
    const forms = document.querySelectorAll("form[data-validate]");

    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add("was-validated");
      });

      // Real-time validation
      const inputs = form.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => {
        input.addEventListener("blur", () => {
          this.validateField(input);
        });

        input.addEventListener("input", () => {
          if (input.classList.contains("is-invalid")) {
            this.validateField(input);
          }
        });
      });
    });
  },

  // Validează un formular
  validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll(
      "input[required], select[required], textarea[required]"
    );

    inputs.forEach((input) => {
      if (!this.validateField(input)) {
        isValid = false;
      }
    });

    return isValid;
  },

  // Validează un câmp individual
  validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      errorMessage = "Acest câmp este obligatoriu.";
    }

    // Email validation
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = "Adresa de email nu este validă.";
      }
    }

    // Phone validation
    if (field.type === "tel" && value) {
      const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
      if (!phoneRegex.test(value)) {
        isValid = false;
        errorMessage = "Numărul de telefon nu este valid.";
      }
    }

    // Password validation
    if (
      field.type === "password" &&
      value &&
      field.hasAttribute("data-min-length")
    ) {
      const minLength = parseInt(field.getAttribute("data-min-length"));
      if (value.length < minLength) {
        isValid = false;
        errorMessage = `Parola trebuie să aibă minim ${minLength} caractere.`;
      }
    }

    // Password confirmation
    if (field.hasAttribute("data-confirm")) {
      const confirmField = document.getElementById(
        field.getAttribute("data-confirm")
      );
      if (confirmField && value !== confirmField.value) {
        isValid = false;
        errorMessage = "Parolele nu se potrivesc.";
      }
    }

    // Custom validation patterns
    if (field.hasAttribute("pattern") && value) {
      const pattern = new RegExp(field.getAttribute("pattern"));
      if (!pattern.test(value)) {
        isValid = false;
        errorMessage =
          field.getAttribute("data-pattern-message") ||
          "Formatul nu este valid.";
      }
    }

    // Update field state
    this.updateFieldValidation(field, isValid, errorMessage);

    return isValid;
  },

  // Actualizează starea validării unui câmp
  updateFieldValidation(field, isValid, errorMessage) {
    const feedbackEl =
      field.parentNode.querySelector(".invalid-feedback") ||
      field.parentNode.querySelector(".form-text");

    if (isValid) {
      field.classList.remove("is-invalid");
      field.classList.add("is-valid");
      if (feedbackEl) {
        feedbackEl.textContent = "";
        feedbackEl.style.display = "none";
      }
    } else {
      field.classList.remove("is-valid");
      field.classList.add("is-invalid");
      if (feedbackEl) {
        feedbackEl.textContent = errorMessage;
        feedbackEl.style.display = "block";
        feedbackEl.classList.add("invalid-feedback");
      } else {
        // Creează element pentru feedback dacă nu există
        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        feedback.textContent = errorMessage;
        field.parentNode.appendChild(feedback);
      }
    }
  },

  // Image galleries
  initImageGalleries() {
    const galleries = document.querySelectorAll(".image-gallery");

    galleries.forEach((gallery) => {
      const mainImage = gallery.querySelector(".main-image img");
      const thumbnails = gallery.querySelectorAll(".thumbnail-img");

      if (!mainImage || !thumbnails.length) return;

      thumbnails.forEach((thumb) => {
        thumb.addEventListener("click", (e) => {
          e.preventDefault();

          // Update main image
          const newSrc = thumb.dataset.fullsize || thumb.src;
          const newAlt = thumb.alt;

          // Fade effect
          mainImage.style.opacity = "0.5";

          setTimeout(() => {
            mainImage.src = newSrc;
            mainImage.alt = newAlt;
            mainImage.style.opacity = "1";
          }, 150);

          // Update active thumbnail
          thumbnails.forEach((t) => t.classList.remove("active"));
          thumb.classList.add("active");
        });
      });
    });
  },

  // Search functionality
  initSearchFunctionality() {
    const searchForms = document.querySelectorAll("form[data-search]");

    searchForms.forEach((form) => {
      const searchInput = form.querySelector(
        'input[type="search"], input[name="search"]'
      );
      if (!searchInput) return;

      let searchTimeout;

      // Live search cu debounce
      searchInput.addEventListener("input", (e) => {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
          if (e.target.value.length >= 3 || e.target.value.length === 0) {
            this.performSearch(form, e.target.value);
          }
        }, this.config.searchDelay);
      });
    });
  },

  // Efectuează căutarea
  performSearch(form, query) {
    const resultsContainer = document.querySelector(
      form.dataset.resultsContainer
    );
    if (!resultsContainer) return;

    // Show loading
    this.showLoading(resultsContainer);

    // Simulare request (înlocuiește cu AJAX real)
    setTimeout(() => {
      this.hideLoading(resultsContainer);
      // Aici ar trebui să fie request-ul real către server
      console.log("Searching for:", query);
    }, 1000);
  },

  // Bootstrap components
  initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: "viewport",
      });
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  },

  // Loading states
  initLoadingStates() {
    // Form submissions
    const forms = document.querySelectorAll("form[data-loading]");

    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (form.checkValidity()) {
          this.showFormLoading(form);
        }
      });
    });

    // Button loading states
    const loadingButtons = document.querySelectorAll("[data-loading-text]");

    loadingButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        this.showButtonLoading(btn);
      });
    });
  },

  // Show form loading
  showFormLoading(form) {
    const submitBtn = form.querySelector(
      'button[type="submit"], input[type="submit"]'
    );
    if (submitBtn) {
      submitBtn.disabled = true;
      const originalText = submitBtn.textContent || submitBtn.value;
      submitBtn.dataset.originalText = originalText;

      const loadingText = submitBtn.dataset.loadingText || "Se procesează...";
      if (submitBtn.tagName === "BUTTON") {
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
      } else {
        submitBtn.value = loadingText;
      }
    }
  },

  // Show button loading
  showButtonLoading(btn) {
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.dataset.originalText = originalText;

    const loadingText = btn.dataset.loadingText || "Se încarcă...";
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
  },

  // Hide loading
  hideLoading(element) {
    element.classList.remove("loading");
    const spinner = element.querySelector(".spinner-border");
    if (spinner) {
      spinner.remove();
    }
  },

  // Show loading overlay
  showLoading(element) {
    element.classList.add("loading");
    if (!element.querySelector(".spinner-border")) {
      const spinner = document.createElement("div");
      spinner.className = "spinner-border text-primary";
      spinner.setAttribute("role", "status");
      element.appendChild(spinner);
    }
  },

  // Lazy loading
  initLazyLoading() {
    if ("IntersectionObserver" in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove("lazy");
            observer.unobserve(img);
          }
        });
      });

      const lazyImages = document.querySelectorAll("img[data-src]");
      lazyImages.forEach((img) => imageObserver.observe(img));
    }
  },

  // Auto-hide alerts
  initAutoHideAlerts() {
    const alerts = document.querySelectorAll(".alert[data-auto-hide]");

    alerts.forEach((alert) => {
      const delay = parseInt(alert.dataset.autoHide) || 5000;

      setTimeout(() => {
        const closeBtn = alert.querySelector(".btn-close");
        if (closeBtn) {
          closeBtn.click();
        } else {
          alert.style.opacity = "0";
          setTimeout(() => alert.remove(), 300);
        }
      }, delay);
    });
  },

  // Smooth scrolling
  initSmoothScrolling() {
    const smoothLinks = document.querySelectorAll('a[href^="#"]');

    smoothLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        const target = document.querySelector(link.getAttribute("href"));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      });
    });
  },

  // Form enhancements
  initFormEnhancements() {
    // Auto-resize textareas
    const textareas = document.querySelectorAll("textarea[data-auto-resize]");
    textareas.forEach((textarea) => {
      textarea.addEventListener("input", () => {
        textarea.style.height = "auto";
        textarea.style.height = textarea.scrollHeight + "px";
      });
    });

    // File input enhancements
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach((input) => {
      input.addEventListener("change", (e) => {
        this.handleFileInputChange(e.target);
      });
    });

    // Number input formatting
    const priceInputs = document.querySelectorAll('input[data-format="price"]');
    priceInputs.forEach((input) => {
      input.addEventListener("input", (e) => {
        this.formatPriceInput(e.target);
      });
    });
  },

  // Handle file input changes
  handleFileInputChange(input) {
    const files = input.files;
    const label = input.nextElementSibling;

    if (files.length > 0) {
      if (files.length === 1) {
        label.textContent = files[0].name;
      } else {
        label.textContent = `${files.length} fișiere selectate`;
      }
    } else {
      label.textContent = label.dataset.originalText || "Alege fișier";
    }

    // Preview pentru imagini
    if (input.accept && input.accept.includes("image/")) {
      this.showImagePreview(input, files);
    }
  },

  // Show image preview
  showImagePreview(input, files) {
    const previewContainer = document.querySelector(input.dataset.preview);
    if (!previewContainer) return;

    previewContainer.innerHTML = "";

    Array.from(files).forEach((file) => {
      if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.createElement("img");
          img.src = e.target.result;
          img.className = "img-thumbnail me-2 mb-2";
          img.style.maxWidth = "100px";
          img.style.maxHeight = "100px";
          previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
      }
    });
  },

  // Format price input
  formatPriceInput(input) {
    let value = input.value.replace(/[^0-9]/g, "");
    if (value) {
      value = parseInt(value).toLocaleString("ro-RO");
    }
    input.value = value;
  },

  // Accessibility improvements
  initAccessibility() {
    // Skip links
    const skipLinks = document.querySelectorAll(".skip-link");
    skipLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute("href"));
        if (target) {
          target.setAttribute("tabindex", "-1");
          target.focus();
        }
      });
    });

    // Keyboard navigation for custom components
    this.initKeyboardNavigation();

    // Focus management for modals
    this.initModalFocusManagement();
  },

  // Keyboard navigation
  initKeyboardNavigation() {
    // Card navigation
    const cards = document.querySelectorAll(".car-card, .card[tabindex]");
    cards.forEach((card) => {
      card.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          const link = card.querySelector("a");
          if (link) {
            e.preventDefault();
            link.click();
          }
        }
      });
    });
  },

  // Modal focus management
  initModalFocusManagement() {
    const modals = document.querySelectorAll(".modal");
    modals.forEach((modal) => {
      modal.addEventListener("shown.bs.modal", () => {
        const firstFocusable = modal.querySelector(
          'input, button, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (firstFocusable) {
          firstFocusable.focus();
        }
      });
    });
  },

  // Handle page load
  handlePageLoad() {
    // Fade in animation
    document.body.classList.add("fade-in");

    // Initialize page-specific functionality
    this.initPageSpecific();
  },

  // Page-specific initialization
  initPageSpecific() {
    const page = document.body.dataset.page;

    switch (page) {
      case "cars":
        this.initCarsPage();
        break;
      case "car-detail":
        this.initCarDetailPage();
        break;
      case "dashboard":
        this.initDashboardPage();
        break;
      case "add-car":
        this.initAddCarPage();
        break;
    }
  },

  // Cars page functionality
  initCarsPage() {
    this.initFilters();
    this.initSorting();
    this.initPagination();
  },

  // Car detail page functionality
  initCarDetailPage() {
    this.initImageGallery();
    this.initContactForm();
    this.initSimilarCars();
  },

  // Dashboard functionality
  initDashboardPage() {
    this.initCharts();
    this.initDataTables();
    this.initQuickActions();
  },

  // Add car form functionality
  initAddCarPage() {
    this.initImageUpload();
    this.initFormSteps();
    this.initPriceCalculator();
  },

  // Filters functionality
  initFilters() {
    const filterForm = document.getElementById("filterForm");
    if (!filterForm) return;

    const filterInputs = filterForm.querySelectorAll("input, select");

    filterInputs.forEach((input) => {
      input.addEventListener("change", () => {
        this.applyFilters();
      });
    });

    // Reset filters
    const resetBtn = filterForm.querySelector("[data-reset-filters]");
    if (resetBtn) {
      resetBtn.addEventListener("click", () => {
        filterForm.reset();
        this.applyFilters();
      });
    }
  },

  // Apply filters
  applyFilters() {
    const filterForm = document.getElementById("filterForm");
    const formData = new FormData(filterForm);

    // Show loading
    this.showLoadingOverlay();

    // AJAX request (simulat)
    setTimeout(() => {
      this.hideLoadingOverlay();
      // Aici ar trebui să fie request-ul real
      console.log("Filters applied:", Object.fromEntries(formData));
    }, 1000);
  },

  // Show/hide loading overlay
  showLoadingOverlay() {
    const overlay = document.getElementById("loadingOverlay");
    if (overlay) {
      overlay.classList.remove("d-none");
    }
  },

  hideLoadingOverlay() {
    const overlay = document.getElementById("loadingOverlay");
    if (overlay) {
      overlay.classList.add("d-none");
    }
  },

  // Utility functions
  utils: {
    // Debounce function
    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    // Throttle function
    throttle(func, limit) {
      let inThrottle;
      return function () {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(() => (inThrottle = false), limit);
        }
      };
    },

    // Format numbers
    formatNumber(num) {
      return new Intl.NumberFormat("ro-RO").format(num);
    },

    // Format currency
    formatCurrency(amount) {
      return new Intl.NumberFormat("ro-RO", {
        style: "currency",
        currency: "EUR",
      }).format(amount);
    },

    // Validate email
    isValidEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    },

    // Validate phone
    isValidPhone(phone) {
      const re = /^[\+]?[0-9\s\-\(\)]{10,}$/;
      return re.test(phone);
    },
  },
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  AutoRares.init();
});

// Export for use in other scripts
window.AutoRares = AutoRares;
