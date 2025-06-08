// Load match statistics
function loadMatchStatistics() {
  const token = localStorage.getItem("user_token");
  console.log(
    "Statistics - token:",
    token ? token.substring(0, 20) + "..." : "no token"
  );

  if (!token) {
    showMessage("Please login first", "error");
    loadPage("login");
    return;
  }

  // Load all statistics
  fetch("http://localhost:8888/sports-league-app/backend/api/statistics", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => {
      console.log("Statistics response status:", response.status);
      return response.json();
    })
    .then((result) => {
      console.log("Statistics result:", result);
      if (result.success) {
        displayStatistics(result.data);
      } else {
        showMessage("Error loading statistics: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error loading statistics:", error);
      showMessage("Error loading statistics", "error");
    });

  // Load top scorers
  fetch(
    "http://localhost:8888/sports-league-app/backend/api/statistics/top-scorers",
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    }
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayTopScorers(result.data);
      }
    })
    .catch((error) => console.error("Error loading top scorers:", error));
}

// Display statistics in the table
function displayStatistics(statistics) {
  const tbody = document.querySelector(".statistics-table tbody");
  if (!tbody) return;

  tbody.innerHTML = "";
  statistics.forEach((stat) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${stat.match_name || "Unknown Match"}</td>
            <td>${stat.player_name || "Unknown Player"}</td>
            <td>${stat.event_type}</td>
            <td>${stat.minute}'</td>
            <td>
                ${
                  checkAdmin()
                    ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteStat(${stat.stat_id})">
                        Delete
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editStat(${stat.stat_id})">
                        Edit
                    </button>
                `
                    : ""
                }
            </td>
        `;
    tbody.appendChild(row);
  });
}

// Display top scorers
function displayTopScorers(scorers) {
  const tbody = document.getElementById("topScorersTable");
  if (!tbody) return;

  tbody.innerHTML = "";
  scorers.slice(0, 5).forEach((scorer) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${scorer.first_name} ${scorer.last_name}</td>
            <td>${scorer.goals}</td>
        `;
    tbody.appendChild(row);
  });
}

// Add new statistic
function addStatistic() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    return;
  }

  const form = document.getElementById("addStatForm");
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  fetch("http://localhost:8888/sports-league-app/backend/api/statistics", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Statistic added successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addStatModal")
        );
        modal.hide();
        loadMatchStatistics();
      } else {
        showMessage("Error adding statistic: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error adding statistic", "error");
    });
}

// Delete statistic (admin only)
function deleteStat(statId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can delete statistics", "error");
    return;
  }

  if (!confirm("Are you sure you want to delete this statistic?")) {
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(
    `http://localhost:8888/sports-league-app/backend/api/statistics/${statId}`,
    {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    }
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Statistic deleted successfully", "success");
        loadMatchStatistics();
      } else {
        showMessage("Error deleting statistic: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error deleting statistic", "error");
    });
}

// Edit statistic (admin only)
function editStat(statId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can edit statistics", "error");
    return;
  }

  // For now, just show a message - you can implement edit modal later
  showMessage("Edit functionality coming soon", "info");
}

// Load matches for select
function loadMatchesForSelect(selectId = "statMatchSelect") {
  const select = document.getElementById(selectId);
  if (!select) return;

  fetch("http://localhost:8888/sports-league-app/backend/api/matches", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        select.innerHTML = '<option value="">Select Match</option>';
        result.data.forEach((match) => {
          select.innerHTML += `
                    <option value="${match.match_id}">
                        ${match.home_team_name} vs ${match.away_team_name} (${match.date_played})
                    </option>
                `;
        });
      }
    })
    .catch((error) => console.error("Error loading matches:", error));
}

// Load players for select
function loadPlayersForSelect(selectId = "statPlayerSelect") {
  const select = document.getElementById(selectId);
  if (!select) return;

  fetch("http://localhost:8888/sports-league-app/backend/api/players", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        select.innerHTML = '<option value="">Select Player</option>';
        result.data.forEach((player) => {
          select.innerHTML += `
                    <option value="${player.player_id}">
                        ${player.first_name} ${player.last_name}
                    </option>
                `;
        });
      }
    })
    .catch((error) => console.error("Error loading players:", error));
}

// Initialize selects when add statistic modal is shown
document
  .getElementById("addStatModal")
  ?.addEventListener("show.bs.modal", function () {
    loadMatchesForSelect();
    loadPlayersForSelect();
  });
