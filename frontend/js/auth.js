// Login Function
function login(event) {
  event.preventDefault();

  const form = document.getElementById("loginForm");
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  fetch(API_BASE + "api/auth/login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        localStorage.setItem("user_token", result.data.token);
        showMessage("Login successful!", "success");
        updateNavigation();
        loadPage("home");
      } else {
        showMessage("Login failed: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Login error:", error);
      showMessage("Login failed: " + error.message, "error");
    });
}

// Register Function
function register(event) {
  event.preventDefault();

  const form = document.getElementById("registerForm");
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());
  const confirmPassword = document.getElementById("regConfirmPassword").value;

  if (data.password !== confirmPassword) {
    showMessage("Passwords do not match!", "error");
    return;
  }

  fetch(API_BASE + "api/auth/register", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Registration successful! Please login.", "success");
        loadPage("login");
      } else {
        showMessage("Registration failed: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Registration error:", error);
      showMessage("Registration failed: " + error.message, "error");
    });
}

// Setup Login Form
function setupLoginForm() {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", login);
  }
}

// Setup Register Form
function setupRegisterForm() {
  const form = document.getElementById("registerForm");
  if (form) {
    form.addEventListener("submit", register);
  }
}

// Initialize Auth
document.addEventListener("DOMContentLoaded", () => {
  updateNavigation();
});
