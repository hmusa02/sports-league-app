console.log("app.js loaded!");

function loadPage(page) {
  console.log("loadPage called with:", page);

  if (!Utils.isLoggedIn() && page !== "login" && page !== "register") {
    console.log("User not logged in, redirecting to login");
    loadPage("login");
    return;
  }

  // Add cache-busting timestamp
  const timestamp = new Date().getTime();
  const url = `views/${page}.html?t=${timestamp}`;

  console.log("Fetching page:", url);

  fetch(url)
    .then((response) => {
      console.log("Fetch response:", response);
      return response.text();
    })
    .then((html) => {
      console.log("HTML loaded for page:", page);
      document.getElementById("mainContent").innerHTML = html;
      console.log(page + " page loaded");

      if (page === "register") {
        console.log("Setting up register form");
        setupRegisterForm();
      } else if (page === "login") {
        console.log("Setting up login form");
        setupLoginForm();
      } else if (page === "teams") {
        loadTeams();
      } else if (page === "matches") {
        loadMatches();
        if (typeof setupMatchModalListeners === "function") {
          setupMatchModalListeners();
        }
      } else if (page === "profile") {
        loadUserProfile();
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
    .catch((err) => {
      console.error("Error loading page:", err);
    });
}

function setupRegisterForm() {
  setTimeout(() => {
    const form = document.getElementById("registerForm");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        const confirmPassword =
          document.getElementById("regConfirmPassword").value;
        if (data.password !== confirmPassword) {
          alert("Passwords do not match!");
          return;
        }

        fetch(
          "http://localhost:8888/sports-league-app/backend/api/auth/register",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
          }
        )
          .then((response) => response.json())
          .then((result) => {
            if (result.success) {
              alert("Registration successful! Please login.");
              loadPage("login");
            } else {
              alert("Registration failed: " + result.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Registration failed: " + error.message);
          });
      });
    }
  }, 100);
}

function setupLoginForm() {
  setTimeout(() => {
    const form = document.getElementById("loginForm");
    if (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        fetch(
          "http://localhost:8888/sports-league-app/backend/api/auth/login",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
          }
        )
          .then((response) => response.json())
          .then((result) => {
            if (result.success) {
              localStorage.setItem("user_token", result.data.token);
              alert("Login successful!");
              updateNavigation();
              loadPage("home");
            } else {
              alert("Login failed: " + result.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Login failed: " + error.message);
          });
      });
    }
  }, 100);
}

function loadUserProfile() {
  const user = Utils.getCurrentUser();
  if (user) {
    const profileUsername = document.getElementById("profileUsername");
    const profileEmail = document.getElementById("profileEmail");

    if (profileUsername) profileUsername.textContent = user.username;
    if (profileEmail) profileEmail.textContent = user.email;
  }
}

function updateNavigation() {
  const user = Utils.getCurrentUser();

  if (Utils.isLoggedIn()) {
    const loginLink = document.querySelector(
      "a[onclick=\"loadPage('login')\"]"
    );
    const registerLink = document.querySelector(
      "a[onclick=\"loadPage('register')\"]"
    );

    if (loginLink) loginLink.style.display = "none";
    if (registerLink) registerLink.style.display = "none";

    const navbarNav = document.querySelector(".navbar-nav");
    if (navbarNav && !document.querySelector(".logout-btn")) {
      const logoutBtn = document.createElement("li");
      logoutBtn.className = "nav-item logout-btn";
      logoutBtn.innerHTML =
        '<a class="nav-link" href="#" onclick="logout()">Logout</a>';
      navbarNav.appendChild(logoutBtn);
    }

    if (user && user.role === "admin") {
      console.log("Admin user detected");
    }
  } else {
    const loginLink = document.querySelector(
      "a[onclick=\"loadPage('login')\"]"
    );
    const registerLink = document.querySelector(
      "a[onclick=\"loadPage('register')\"]"
    );
    const logoutBtn = document.querySelector(".logout-btn");

    if (loginLink) loginLink.style.display = "block";
    if (registerLink) registerLink.style.display = "block";
    if (logoutBtn) logoutBtn.remove();
  }
}

function logout() {
  localStorage.removeItem("user_token");
  updateNavigation();
  loadPage("login");
}

// Initialize App
document.addEventListener("DOMContentLoaded", () => {
  // Update navigation based on auth status
  updateNavigation();

  // Load initial page
  const token = localStorage.getItem("user_token");
  if (token) {
    // If user is logged in, load home page
    loadPage("home");
  } else {
    // If user is not logged in, also load home page
    // They will be redirected to login if they try to access protected resources
    loadPage("home");
  }

  // Setup page load event listeners
  document.querySelectorAll("[data-page]").forEach((element) => {
    element.addEventListener("click", (e) => {
      e.preventDefault();
      const page = e.target.getAttribute("data-page");
      loadPage(page);
    });
  });
});
