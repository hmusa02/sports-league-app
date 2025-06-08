// Load all players
function loadPlayers() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    loadPage("login");
    return;
  }

  fetch("http://localhost:8888/sports-league-app/backend/api/players", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayPlayers(result.data);
      } else {
        showMessage("Error loading players: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error loading players:", error);
      showMessage("Error loading players", "error");
    });
}

// Display players in the table
function displayPlayers(players) {
  const tbody = document.querySelector(".players-table tbody");
  if (!tbody) return;

  tbody.innerHTML = "";
  players.forEach((player) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${player.first_name} ${player.last_name}</td>
            <td>${player.team_name || "No Team"}</td>
            <td>${player.position}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewPlayerStats(${
                  player.player_id
                })">
                    View Stats
                </button>
            </td>
            <td>
                ${
                  checkAdmin()
                    ? `
                    <button class="btn btn-sm btn-danger" onclick="deletePlayer(${player.player_id})">
                        Delete
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editPlayer(${player.player_id})">
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

// Add new player
function addPlayer() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    return;
  }

  if (!checkAdmin()) {
    showMessage("Only administrators can add players", "error");
    return;
  }

  const form = document.getElementById("addPlayerForm");
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  fetch("http://localhost:8888/sports-league-app/backend/api/players", {
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
        showMessage("Player added successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addPlayerModal")
        );
        modal.hide();
        loadPlayers();
      } else {
        showMessage("Error adding player: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error adding player", "error");
    });
}

// Delete player
function deletePlayer(playerId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can delete players", "error");
    return;
  }

  if (!confirm("Are you sure you want to delete this player?")) {
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(
    `http://localhost:8888/sports-league-app/backend/api/players/${playerId}`,
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
        showMessage("Player deleted successfully", "success");
        loadPlayers();
      } else {
        showMessage("Error deleting player: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error deleting player", "error");
    });
}

// Edit player
function editPlayer(playerId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can edit players", "error");
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(
    `http://localhost:8888/sports-league-app/backend/api/players/${playerId}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    }
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        const player = result.data;
        document.getElementById("editPlayerFirstName").value =
          player.first_name;
        document.getElementById("editPlayerLastName").value = player.last_name;
        document.getElementById("editPlayerPosition").value = player.position;
        document.getElementById("editPlayerTeamSelect").value = player.team_id;

        // Store player ID for update
        document.getElementById("editPlayerForm").dataset.playerId = playerId;

        // Show edit modal
        const editModal = new bootstrap.Modal(
          document.getElementById("editPlayerModal")
        );
        editModal.show();
      } else {
        showMessage("Error loading player details: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error loading player details", "error");
    });
}

// Update player
function updatePlayer() {
  const token = localStorage.getItem("user_token");
  const form = document.getElementById("editPlayerForm");
  const playerId = form.dataset.playerId;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  fetch(
    `http://localhost:8888/sports-league-app/backend/api/players/${playerId}`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(data),
    }
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Player updated successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editPlayerModal")
        );
        modal.hide();
        loadPlayers();
      } else {
        showMessage("Error updating player: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error updating player", "error");
    });
}

// View player statistics
function viewPlayerStats(playerId) {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    return;
  }

  fetch(
    `http://localhost:8888/sports-league-app/backend/api/statistics/player/${playerId}/summary`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    }
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayPlayerStats(result.data);
      } else {
        showMessage(
          "Error loading player statistics: " + result.message,
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error loading player statistics", "error");
    });
}

// Display player statistics in modal
function displayPlayerStats(stats) {
  const modal = document.getElementById("playerStatsModal");
  const modalBody = modal.querySelector(".modal-body");
  const modalTitle = modal.querySelector(".modal-title");

  modalTitle.textContent = `Statistics for ${stats.player.first_name} ${stats.player.last_name}`;
  modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Team:</strong> ${
                  stats.player.team_name || "No Team"
                }</p>
                <p><strong>Position:</strong> ${stats.player.position}</p>
                <p><strong>Jersey Number:</strong> ${
                  stats.player.jersey_number
                }</p>
            </div>
            <div class="col-md-6">
                <p><strong>Total Matches:</strong> ${stats.total_matches}</p>
                <p><strong>Goals:</strong> ${stats.goals}</p>
                <p><strong>Assists:</strong> ${stats.assists}</p>
                <p><strong>Yellow Cards:</strong> ${stats.yellow_cards}</p>
                <p><strong>Red Cards:</strong> ${stats.red_cards}</p>
            </div>
        </div>
    `;

  const statsModal = new bootstrap.Modal(modal);
  statsModal.show();
}

// Load teams for select
function loadTeamsForSelect(selectId = "playerTeamSelect") {
  const token = localStorage.getItem("user_token");
  const select = document.getElementById(selectId);
  if (!select) return;

  fetch("http://localhost:8888/sports-league-app/backend/api/teams", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        select.innerHTML = '<option value="">Select Team</option>';
        result.data.forEach((team) => {
          select.innerHTML += `
                    <option value="${team.team_id}">${team.team_name}</option>
                `;
        });
      }
    })
    .catch((error) => console.error("Error loading teams:", error));
}

// Initialize selects when add player modal is shown
document
  .getElementById("addPlayerModal")
  ?.addEventListener("show.bs.modal", function () {
    loadTeamsForSelect();
  });
