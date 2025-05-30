console.log("app.js loaded!");

function loadPage(page) {
  console.log("loadPage called with:", page);

  if (!Utils.isLoggedIn() && page !== "login" && page !== "register") {
    console.log("User not logged in, redirecting to login");
    loadPage("login");
    return;
  }

  console.log("Fetching page:", `views/${page}.html`);

  fetch(`views/${page}.html`)
    .then((response) => {
      console.log("Fetch response:", response);
      return response.text();
    })
    .then((html) => {
      console.log("HTML loaded for page:", page);
      document.getElementById("content").innerHTML = html;
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
      } else if (page === "profile") {
        loadUserProfile();
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

function loadTeams() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    alert("Please login first");
    loadPage("login");
    return;
  }

  fetch("http://localhost:8888/sports-league-app/backend/api/teams", {
    method: "GET",
    headers: {
      Authentication: token,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayTeams(result.data);
      } else {
        alert("Error loading teams: " + result.message);
      }
    })
    .catch((error) => {
      console.error("Error loading teams:", error);
      alert("Error loading teams");
    });
}

function displayTeams(teams) {
  const teamsTable = document.querySelector(".teams-table tbody");
  if (teamsTable) {
    teamsTable.innerHTML = "";
    teams.forEach((team) => {
      const row = `
                <tr>
                    <td>${team.team_name}</td>
                    <td>${team.city || "N/A"}</td>
                    <td>${team.coach_name || "No Coach"}</td>
                </tr>
            `;
      teamsTable.innerHTML += row;
    });
  }
}

function loadMatches() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    alert("Please login first");
    loadPage("login");
    return;
  }

  fetch("http://localhost:8888/sports-league-app/backend/api/matches", {
    method: "GET",
    headers: {
      Authentication: token,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayMatches(result.data);
      } else {
        alert("Error loading matches: " + result.message);
      }
    })
    .catch((error) => {
      console.error("Error loading matches:", error);
      alert("Error loading matches");
    });
}

function displayMatches(matches) {
  const matchesTable = document.querySelector(".matches-table tbody");
  if (matchesTable) {
    matchesTable.innerHTML = "";
    matches.forEach((match) => {
      const row = `
                <tr>
                    <td>${match.date_played}</td>
                    <td>${match.home_team_name}</td>
                    <td>${match.away_team_name}</td>
                    <td>${match.score_home || 0} - ${match.score_away || 0}</td>
                </tr>
            `;
      matchesTable.innerHTML += row;
    });
  }
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

window.onload = () => {
  if (Utils.isLoggedIn()) {
    updateNavigation();
    loadPage("home");
  } else {
    loadPage("login");
  }
  console.log("App initialized");
};
