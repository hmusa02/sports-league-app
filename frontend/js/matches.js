// Matches functionality
function loadMatches() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    loadPage("login");
    return;
  }

  fetch("http://localhost:8888/sports-league-app/backend/api/matches", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        displayMatches(result.data);
        showAdminControls();
      } else {
        showMessage("Error loading matches: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error loading matches:", error);
      showMessage("Error loading matches", "error");
    });
}

function displayMatches(matches) {
  const tbody = document.querySelector(".matches-table tbody");
  if (!tbody) return;

  tbody.innerHTML = "";
  matches.forEach((match) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${match.home_team_name} vs ${match.away_team_name}</td>
            <td>${
              match.date_played ? match.date_played.split(" ")[0] : "TBD"
            }</td>
            <td>${
              match.date_played ? match.date_played.split(" ")[1] : "TBD"
            }</td>
            <td>${match.score_home ?? "-"} - ${match.score_away ?? "-"}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewMatchStats(${
                  match.match_id
                })">
                    View Stats
                </button>
            </td>
            <td>
                ${
                  checkAdmin()
                    ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteMatch(${match.match_id})">
                        Delete
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editMatch(${match.match_id})">
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

function addMatch() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    return;
  }

  if (!checkAdmin()) {
    showMessage("Only administrators can add matches", "error");
    return;
  }

  const form = document.getElementById("addMatchForm");
  const formData = new FormData(form);

  // Get date and time values
  const matchDate = formData.get("match_date");
  const matchTime = formData.get("match_time");

  // Combine date and time into MySQL datetime format
  const dateTime =
    matchDate && matchTime ? `${matchDate} ${matchTime}:00` : null;

  const data = {
    home_team_id: parseInt(formData.get("home_team_id")),
    away_team_id: parseInt(formData.get("away_team_id")),
    date_played: dateTime,
    score_home: null,
    score_away: null,
  };

  console.log("Sending match data:", data); // Add logging

  // Validate that team IDs are numbers
  if (!data.home_team_id || !data.away_team_id) {
    showMessage("Please enter valid team IDs", "error");
    return;
  }

  if (data.home_team_id === data.away_team_id) {
    showMessage("Home team and away team must be different", "error");
    return;
  }

  fetch("http://localhost:8888/sports-league-app/backend/api/matches", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(data),
  })
    .then(async (response) => {
      const result = await response.json();
      console.log("Backend response:", result);

      if (result.success) {
        showMessage("Match added successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addMatchModal")
        );
        modal.hide();
        form.reset();
        loadMatches();
      } else {
        console.error("Backend error:", result);
        showMessage("Error adding match: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Network error:", error);
      showMessage("Error adding match: Network error", "error");
    });
}

function deleteMatch(matchId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can delete matches", "error");
    return;
  }

  if (!confirm("Are you sure you want to delete this match?")) {
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(
    `http://localhost:8888/sports-league-app/backend/api/matches/${matchId}`,
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
        showMessage("Match deleted successfully", "success");
        loadMatches();
      } else {
        showMessage("Error deleting match: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error deleting match", "error");
    });
}

function editMatch(matchId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can edit matches", "error");
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(
    `http://localhost:8888/sports-league-app/backend/api/matches/${matchId}`,
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
        const match = result.data;
        document.getElementById("editHomeTeam").value = match.home_team_id;
        document.getElementById("editAwayTeam").value = match.away_team_id;
        document.getElementById("editMatchDate").value = match.date_played
          ? match.date_played.split(" ")[0]
          : "";
        document.getElementById("editMatchTime").value = match.date_played
          ? match.date_played.split(" ")[1]
          : "";
        document.getElementById("editScoreHome").value = match.score_home || "";
        document.getElementById("editScoreAway").value = match.score_away || "";

        // Store match ID for update
        document.getElementById("editMatchForm").dataset.matchId = matchId;

        // Show edit modal
        const editModal = new bootstrap.Modal(
          document.getElementById("editMatchModal")
        );
        editModal.show();
      } else {
        showMessage("Error loading match details: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error loading match details", "error");
    });
}

function updateMatch() {
  const token = localStorage.getItem("user_token");
  const form = document.getElementById("editMatchForm");
  const matchId = form.dataset.matchId;
  const formData = new FormData(form);

  // Get date and time values
  const matchDate = formData.get("match_date");
  const matchTime = formData.get("match_time");

  // Combine date and time into MySQL datetime format
  const dateTime =
    matchDate && matchTime ? `${matchDate} ${matchTime}:00` : null;

  const data = {
    home_team_id: parseInt(formData.get("home_team_id")),
    away_team_id: parseInt(formData.get("away_team_id")),
    date_played: dateTime,
    score_home: formData.get("score_home")
      ? parseInt(formData.get("score_home"))
      : null,
    score_away: formData.get("score_away")
      ? parseInt(formData.get("score_away"))
      : null,
  };

  console.log("Updating match with data:", data);

  fetch(
    `http://localhost:8888/sports-league-app/backend/api/matches/${matchId}`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(data),
    }
  )
    .then(async (response) => {
      const result = await response.json();
      console.log("Update match response:", result);

      if (result.success) {
        showMessage("Match updated successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editMatchModal")
        );
        modal.hide();
        loadMatches();
      } else {
        console.error("Backend error:", result);
        showMessage("Error updating match: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Network error:", error);
      showMessage("Error updating match: Network error", "error");
    });
}

function loadTeamsForSelect() {
  console.log("loadTeamsForSelect called...");
  const token = localStorage.getItem("user_token");

  fetch("http://localhost:8888/sports-league-app/backend/api/teams", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      console.log("Teams loaded:", result);
      if (result.success && Array.isArray(result.data)) {
        console.log("Removing existing team lists...");
        // Remove any existing team lists
        document.querySelectorAll(".team-list").forEach((el) => el.remove());

        console.log("Creating new team list...");
        // Create a list of teams for reference
        const teamsList = document.createElement("div");
        teamsList.className = "team-list";
        teamsList.innerHTML = "<h6>Available Teams:</h6>";
        const ul = document.createElement("ul");
        result.data.forEach((team) => {
          ul.innerHTML += `<li>ID: ${team.team_id} - ${team.team_name}</li>`;
        });
        teamsList.appendChild(ul);

        console.log("Adding team lists to input fields...");
        // Add the teams list below both input fields in add modal
        const homeTeamInput = document.getElementById("homeTeam");
        const awayTeamInput = document.getElementById("awayTeam");

        console.log("Home team input found:", !!homeTeamInput);
        console.log("Away team input found:", !!awayTeamInput);

        if (homeTeamInput) {
          const homeTeamHelp = homeTeamInput.nextElementSibling;
          if (homeTeamHelp) {
            homeTeamHelp.after(teamsList.cloneNode(true));
          }
        }

        if (awayTeamInput) {
          const awayTeamHelp = awayTeamInput.nextElementSibling;
          if (awayTeamHelp) {
            awayTeamHelp.after(teamsList.cloneNode(true));
          }
        }

        // Add the teams list below both input fields in edit modal
        const editHomeTeamInput = document.getElementById("editHomeTeam");
        const editAwayTeamInput = document.getElementById("editAwayTeam");

        console.log("Edit home team input found:", !!editHomeTeamInput);
        console.log("Edit away team input found:", !!editAwayTeamInput);

        if (editHomeTeamInput) {
          const editHomeTeamHelp = editHomeTeamInput.nextElementSibling;
          if (editHomeTeamHelp) {
            editHomeTeamHelp.after(teamsList.cloneNode(true));
          }
        }

        if (editAwayTeamInput) {
          const editAwayTeamHelp = editAwayTeamInput.nextElementSibling;
          if (editAwayTeamHelp) {
            editAwayTeamHelp.after(teamsList.cloneNode(true));
          }
        }

        console.log("Team lists setup complete!");
      } else {
        console.log("No teams data or error:", result);
      }
    })
    .catch((error) => {
      console.error("Error loading teams:", error);
      showMessage("Error loading teams", "error");
    });
}

// Helper function to format date
function formatDate(dateString) {
  if (!dateString) return "TBD";
  const date = new Date(dateString);
  return date.toLocaleDateString() + " " + date.toLocaleTimeString();
}

function setupMatchModalListeners() {
  console.log("Setting up match modal listeners...");

  const addModal = document.getElementById("addMatchModal");
  const editModal = document.getElementById("editMatchModal");

  console.log("Add modal found:", !!addModal);
  console.log("Edit modal found:", !!editModal);

  if (addModal) {
    addModal.addEventListener("show.bs.modal", function () {
      console.log("Add match modal opening, loading teams...");
      loadTeamsForSelect();
    });
  }

  if (editModal) {
    editModal.addEventListener("show.bs.modal", function () {
      console.log("Edit match modal opening, loading teams...");
      loadTeamsForSelect();
    });
  }

  // If modals exist right now, set up teams list immediately
  if (addModal || editModal) {
    console.log("Setting up teams list immediately...");
    loadTeamsForSelect();
  }
}
