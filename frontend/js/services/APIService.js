class APIService {
  constructor() {
    // Automatically detect if we're in production (Railway) or development (localhost)
    this.baseURL = this.getBaseURL();
    this.token = localStorage.getItem("user_token");
  }

  // Detect the correct base URL
  getBaseURL() {
    const hostname = window.location.hostname;

    // Production (Railway deployment)
    if (
      hostname.includes("railway.app") ||
      hostname.includes("up.railway.app")
    ) {
      return `${window.location.origin}/api`;
    }

    // Development (MAMP/localhost)
    if (hostname === "localhost" || hostname === "127.0.0.1") {
      return "http://localhost:8888/sports-league-app/backend/api";
    }

    // Fallback to relative path
    return "/api";
  }

  // Set authorization token
  setToken(token) {
    this.token = token;
    if (token) {
      localStorage.setItem("user_token", token);
    } else {
      localStorage.removeItem("user_token");
    }
  }

  // Get authorization headers
  getHeaders(includeAuth = true) {
    const headers = {
      "Content-Type": "application/json",
    };

    if (includeAuth && this.token) {
      headers["Authorization"] = `Bearer ${this.token}`;
    }

    return headers;
  }

  // Generic GET request
  async get(endpoint, requireAuth = true) {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: "GET",
        headers: this.getHeaders(requireAuth),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("GET request failed:", error);
      throw error;
    }
  }

  // Generic POST request
  async post(endpoint, data, requireAuth = true) {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: "POST",
        headers: this.getHeaders(requireAuth),
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("POST request failed:", error);
      throw error;
    }
  }

  // Generic PUT request
  async put(endpoint, data, requireAuth = true) {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: "PUT",
        headers: this.getHeaders(requireAuth),
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("PUT request failed:", error);
      throw error;
    }
  }

  // Generic DELETE request
  async delete(endpoint, requireAuth = true) {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: "DELETE",
        headers: this.getHeaders(requireAuth),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("DELETE request failed:", error);
      throw error;
    }
  }
}

// Create a singleton instance
const apiService = new APIService();

// Export for use in other files
window.APIService = apiService;
