// JWT Parser
function parseJwt(token) {
  if (!token) return null;
  try {
    const payload = token.split(".")[1];
    const decoded = atob(payload);
    return JSON.parse(decoded);
  } catch (e) {
    console.error("Error parsing JWT:", e);
    return null;
  }
}

// Show Message
function showMessage(message, type = "info") {
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.role = "alert";
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

  const container = document.querySelector(".container");
  if (container) {
    container.insertBefore(alertDiv, container.firstChild);
  }

  // Auto dismiss after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

// Load Page Content
function loadPage(page) {
  const token = localStorage.getItem("user_token");
  const mainContent = document.getElementById("mainContent");

  // Update active nav link
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.classList.remove("active");
    if (link.getAttribute("data-page") === page) {
      link.classList.add("active");
    }
  });

  // Load page content
  fetch(`views/${page}.html`)
    .then((response) => response.text())
    .then((html) => {
      mainContent.innerHTML = html;

      // Initialize any scripts needed for the page
      if (page === "matches") {
        loadMatches();
        if (typeof setupMatchModalListeners === "function") {
          setupMatchModalListeners();
        }
      } else if (page === "teams") {
        loadTeams();
      } else if (page === "players") {
        loadPlayers();
      } else if (page === "statistics") {
        loadMatchStatistics();
      }

      // Show/hide admin controls
      const adminControls = document.getElementById("adminControls");
      if (adminControls) {
        adminControls.style.display = checkAdmin() ? "block" : "none";
      }
    })
    .catch((error) => {
      console.error("Error loading page:", error);
      showMessage("Error loading page", "error");
    });
}

// Format Date
function formatDate(dateString) {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return date.toLocaleDateString();
}

// Format Time
function formatTime(timeString) {
  if (!timeString) return "N/A";
  return timeString;
}

// Check Authentication
function checkAuth() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    loadPage("login");
    return false;
  }

  const decoded = parseJwt(token);
  if (!decoded || !decoded.user) {
    showMessage("Invalid token", "error");
    localStorage.removeItem("user_token");
    loadPage("login");
    return false;
  }

  return true;
}

// Check Admin Role
function checkAdmin() {
  const token = localStorage.getItem("user_token");
  if (!token) return false;

  try {
    const decoded = parseJwt(token);
    console.log("Checking admin role. Decoded token:", decoded);
    const isAdmin = decoded && decoded.user && decoded.user.role === "admin";
    console.log("Is admin?", isAdmin);
    return isAdmin;
  } catch (e) {
    console.error("Error checking admin role:", e);
    return false;
  }
}

// Update Navigation Based on Auth
function updateNavigation() {
  const token = localStorage.getItem("user_token");
  const user = parseJwt(token);
  const navbarNav = document.querySelector(".navbar-nav");
  const authButtons = document.getElementById("authButtons");

  if (token && user && user.user) {
    // User is logged in
    if (authButtons) {
      authButtons.innerHTML = `
        <div class="d-flex align-items-center">
          <span class="text-light me-3">Welcome, ${user.user.email}</span>
          <button class="btn btn-outline-light" onclick="logout()">Logout</button>
        </div>
      `;
    }

    // Show/hide admin controls
    const adminControls = document.querySelectorAll(".admin-controls");
    adminControls.forEach((control) => {
      if (user.user.role === "admin") {
        control.style.display = "block";
      } else {
        control.style.display = "none";
      }
    });
  } else {
    // User is not logged in
    if (authButtons) {
      authButtons.innerHTML = `
        <button class="btn btn-outline-light me-2" onclick="loadPage('login')">Login</button>
        <button class="btn btn-light" onclick="loadPage('register')">Register</button>
      `;
    }

    // Hide all admin controls
    const adminControls = document.querySelectorAll(".admin-controls");
    adminControls.forEach((control) => {
      control.style.display = "none";
    });
  }
}

// Utility functions object
const Utils = {
  isLoggedIn() {
    const token = localStorage.getItem("user_token");
    if (!token) return false;

    try {
      const decoded = parseJwt(token);
      return decoded && decoded.user;
    } catch (e) {
      console.error("Error checking login status:", e);
      return false;
    }
  },

  getCurrentUser() {
    const token = localStorage.getItem("user_token");
    if (!token) return null;

    try {
      const decoded = parseJwt(token);
      console.log("Decoded token:", decoded);
      return decoded.user;
    } catch (e) {
      console.error("Error getting current user:", e);
      return null;
    }
  },

  getAuthHeader() {
    const token = localStorage.getItem("user_token");
    return token ? `Bearer ${token}` : null;
  },

  logout() {
    localStorage.removeItem("user_token");
    showMessage("Logged out successfully", "success");
    loadPage("login");
  },
};

function showAdminControls() {
  document.querySelectorAll(".admin-controls").forEach((el) => {
    el.style.display = checkAdmin() ? "block" : "none";
  });
}
