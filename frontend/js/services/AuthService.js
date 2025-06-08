class AuthService {
  constructor() {
    this.currentUser = null;
    this.loadUserFromStorage();
  }

  // Load user data from localStorage
  loadUserFromStorage() {
    const userData = localStorage.getItem("user_data");
    if (userData) {
      try {
        this.currentUser = JSON.parse(userData);
      } catch (error) {
        console.error("Error parsing user data:", error);
        this.logout();
      }
    }
  }

  // Save user data to localStorage
  saveUserToStorage(userData) {
    this.currentUser = userData;
    localStorage.setItem("user_data", JSON.stringify(userData));
  }

  // Login user
  async login(email, password) {
    try {
      // Validate input
      if (!this.validateEmail(email)) {
        throw new Error("Invalid email format");
      }

      if (!password || password.length < 6) {
        throw new Error("Password must be at least 6 characters long");
      }

      const result = await APIService.post(
        "/auth/login",
        {
          email: email.trim(),
          password: password,
        },
        false
      );

      if (result.success) {
        // Save token and user data
        APIService.setToken(result.token);
        this.saveUserToStorage(result.user);
        return result;
      } else {
        throw new Error(result.message || "Login failed");
      }
    } catch (error) {
      console.error("Login error:", error);
      throw error;
    }
  }

  // Register user
  async register(userData) {
    try {
      // Validate input
      const validation = this.validateRegistrationData(userData);
      if (!validation.isValid) {
        throw new Error(validation.message);
      }

      const result = await APIService.post("/auth/register", userData, false);

      if (result.success) {
        return result;
      } else {
        throw new Error(result.message || "Registration failed");
      }
    } catch (error) {
      console.error("Registration error:", error);
      throw error;
    }
  }

  // Logout user
  logout() {
    this.currentUser = null;
    APIService.setToken(null);
    localStorage.removeItem("user_data");
    localStorage.removeItem("user_token");
  }

  // Check if user is logged in
  isLoggedIn() {
    return this.currentUser !== null && APIService.token !== null;
  }

  // Check if user is admin
  isAdmin() {
    return this.currentUser && this.currentUser.role === "admin";
  }

  // Get current user
  getCurrentUser() {
    return this.currentUser;
  }

  // Validate email format
  validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Validate registration data
  validateRegistrationData(data) {
    const errors = [];

    // First name validation
    if (!data.first_name || data.first_name.trim().length < 2) {
      errors.push("First name must be at least 2 characters long");
    }

    // Last name validation
    if (!data.last_name || data.last_name.trim().length < 2) {
      errors.push("Last name must be at least 2 characters long");
    }

    // Email validation
    if (!data.email || !this.validateEmail(data.email)) {
      errors.push("Please enter a valid email address");
    }

    // Password validation
    if (!data.password || data.password.length < 6) {
      errors.push("Password must be at least 6 characters long");
    }

    // Password confirmation
    if (data.password !== data.confirm_password) {
      errors.push("Passwords do not match");
    }

    return {
      isValid: errors.length === 0,
      message: errors.join("; "),
    };
  }

  // Sanitize user input to prevent XSS
  sanitizeInput(input) {
    if (typeof input !== "string") return input;

    return input
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#x27;")
      .replace(/\//g, "&#x2F;");
  }
}

// Create singleton instance
const authService = new AuthService();

// Export for use in other files
window.AuthService = authService;
