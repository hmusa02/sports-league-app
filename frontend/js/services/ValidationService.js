class ValidationService {
  constructor() {
    this.errorClass = "is-invalid";
    this.successClass = "is-valid";
  }

  // Add real-time validation to a form
  addFormValidation(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Add validation on submit
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (this.validateForm(formId)) {
        this.handleFormSubmit(form);
      }
    });

    // Add real-time validation to inputs
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("blur", () => {
        this.validateField(input);
      });

      input.addEventListener("input", () => {
        this.clearFieldError(input);
      });
    });
  }

  // Validate entire form
  validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

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
  }

  // Validate individual field
  validateField(field) {
    const fieldType = field.type || field.tagName.toLowerCase();
    const value = field.value.trim();
    const fieldName = field.name || field.id;

    // Clear previous validation
    this.clearFieldError(field);

    let isValid = true;
    let errorMessage = "";

    // Required field validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      errorMessage = `${this.getFieldLabel(field)} is required`;
    }
    // Type-specific validation
    else if (value) {
      switch (fieldType) {
        case "email":
          if (!this.isValidEmail(value)) {
            isValid = false;
            errorMessage = "Please enter a valid email address";
          }
          break;

        case "password":
          if (value.length < 6) {
            isValid = false;
            errorMessage = "Password must be at least 6 characters long";
          }
          break;

        case "number":
          const min = field.min ? parseInt(field.min) : null;
          const max = field.max ? parseInt(field.max) : null;
          const numValue = parseInt(value);

          if (isNaN(numValue)) {
            isValid = false;
            errorMessage = "Please enter a valid number";
          } else if (min !== null && numValue < min) {
            isValid = false;
            errorMessage = `Value must be at least ${min}`;
          } else if (max !== null && numValue > max) {
            isValid = false;
            errorMessage = `Value must be no more than ${max}`;
          }
          break;

        case "text":
          const minLength = field.getAttribute("minlength");
          const maxLength = field.getAttribute("maxlength");

          if (minLength && value.length < parseInt(minLength)) {
            isValid = false;
            errorMessage = `${this.getFieldLabel(
              field
            )} must be at least ${minLength} characters long`;
          } else if (maxLength && value.length > parseInt(maxLength)) {
            isValid = false;
            errorMessage = `${this.getFieldLabel(
              field
            )} must be no more than ${maxLength} characters long`;
          }
          break;

        case "select":
          if (!value || value === "") {
            isValid = false;
            errorMessage = `Please select a ${this.getFieldLabel(
              field
            ).toLowerCase()}`;
          }
          break;
      }
    }

    // Special validations
    if (fieldName === "confirm_password") {
      const passwordField = document.querySelector('input[name="password"]');
      if (passwordField && value !== passwordField.value) {
        isValid = false;
        errorMessage = "Passwords do not match";
      }
    }

    // Apply validation result
    if (isValid) {
      this.setFieldSuccess(field);
    } else {
      this.setFieldError(field, errorMessage);
    }

    return isValid;
  }

  // Set field as valid
  setFieldSuccess(field) {
    field.classList.remove(this.errorClass);
    field.classList.add(this.successClass);
    this.removeErrorMessage(field);
  }

  // Set field as invalid
  setFieldError(field, message) {
    field.classList.remove(this.successClass);
    field.classList.add(this.errorClass);
    this.showErrorMessage(field, message);
  }

  // Clear field validation
  clearFieldError(field) {
    field.classList.remove(this.errorClass, this.successClass);
    this.removeErrorMessage(field);
  }

  // Show error message
  showErrorMessage(field, message) {
    this.removeErrorMessage(field);

    const errorDiv = document.createElement("div");
    errorDiv.className = "invalid-feedback";
    errorDiv.textContent = message;
    errorDiv.setAttribute("data-field-error", field.name || field.id);

    field.parentNode.appendChild(errorDiv);
  }

  // Remove error message
  removeErrorMessage(field) {
    const errorDiv = field.parentNode.querySelector(
      `[data-field-error="${field.name || field.id}"]`
    );
    if (errorDiv) {
      errorDiv.remove();
    }
  }

  // Get field label
  getFieldLabel(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    if (label) {
      return label.textContent.replace("*", "").trim();
    }

    return field.name || field.id || "Field";
  }

  // Email validation
  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Handle form submission
  handleFormSubmit(form) {
    // This should be overridden by specific controllers
    console.log("Form submitted:", form.id);
  }

  // Show form-level error message
  showFormError(formId, message) {
    const form = document.getElementById(formId);
    if (!form) return;

    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-danger";
    alertDiv.textContent = message;
    alertDiv.setAttribute("data-form-alert", "error");

    // Remove existing alerts
    const existingAlert = form.querySelector("[data-form-alert]");
    if (existingAlert) {
      existingAlert.remove();
    }

    form.insertBefore(alertDiv, form.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      alertDiv.remove();
    }, 5000);
  }

  // Show form-level success message
  showFormSuccess(formId, message) {
    const form = document.getElementById(formId);
    if (!form) return;

    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-success";
    alertDiv.textContent = message;
    alertDiv.setAttribute("data-form-alert", "success");

    // Remove existing alerts
    const existingAlert = form.querySelector("[data-form-alert]");
    if (existingAlert) {
      existingAlert.remove();
    }

    form.insertBefore(alertDiv, form.firstChild);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      alertDiv.remove();
    }, 3000);
  }

  // Email validation
  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
}

// Create singleton instance
const validationService = new ValidationService();

// Export for use in other files
window.ValidationService = validationService;
