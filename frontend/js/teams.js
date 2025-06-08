// Load all teams
function loadTeams() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    loadPage("login");
    return;
  }

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
        displayTeams(result.data);
        showAdminControls();
      } else {
        showMessage("Error loading teams: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error loading teams:", error);
      showMessage("Error loading teams", "error");
    });
}

// Display teams in the table
function displayTeams(teams) {
  const tbody = document.querySelector(".teams-table tbody");
  if (!tbody) return;

  tbody.innerHTML = "";
  teams.forEach((team) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${team.team_name}</td>
            <td>${team.city || "N/A"}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewTeamStats(${
                  team.team_id
                })">
                    View Stats
                </button>
            </td>
            <td>
                ${
                  checkAdmin()
                    ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteTeam(${team.team_id})">
                        Delete
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editTeam(${team.team_id})">
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

// Add new team
function addTeam() {
  const token = localStorage.getItem("user_token");
  if (!token) {
    showMessage("Please login first", "error");
    return;
  }

  if (!checkAdmin()) {
    showMessage("Only administrators can add teams", "error");
    return;
  }

  const form = document.getElementById("addTeamForm");
  const formData = new FormData(form);
  const data = {
    team_name: formData.get("team_name").trim(),
    city: formData.get("city").trim(),
  };

  console.log("Sending team data:", data);

  fetch("http://localhost:8888/sports-league-app/backend/api/teams", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(data),
  })
    .then(async (response) => {
      const text = await response.text();
      let result;
      try {
        result = JSON.parse(text);
      } catch (e) {
        showMessage("Server error: " + text, "error");
        console.error("Raw server response:", text);
        return;
      }
      if (result.success) {
        showMessage("Team added successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addTeamModal")
        );
        modal.hide();
        form.reset();
        loadTeams();
      } else {
        showMessage("Error adding team: " + result.message, "error");
        console.error("Backend error:", result);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error adding team", "error");
    });
}

// Delete team
function deleteTeam(teamId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can delete teams", "error");
    return;
  }

  if (!confirm("Are you sure you want to delete this team?")) {
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(`http://localhost:8888/sports-league-app/backend/api/teams/${teamId}`, {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Team deleted successfully", "success");
        loadTeams();
      } else {
        showMessage("Error deleting team: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error deleting team", "error");
    });
}

// Edit team
function editTeam(teamId) {
  if (!checkAdmin()) {
    showMessage("Only administrators can edit teams", "error");
    return;
  }

  const token = localStorage.getItem("user_token");
  fetch(`http://localhost:8888/sports-league-app/backend/api/teams/${teamId}`, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        const team = result.data;
        document.getElementById("editTeamName").value = team.name;
        document.getElementById("editTeamCity").value = team.city;

        // Store team ID for update
        document.getElementById("editTeamForm").dataset.teamId = teamId;

        // Show edit modal
        const editModal = new bootstrap.Modal(
          document.getElementById("editTeamModal")
        );
        editModal.show();
      } else {
        showMessage("Error loading team details: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error loading team details", "error");
    });
}

// Update team
function updateTeam() {
  const token = localStorage.getItem("user_token");
  const form = document.getElementById("editTeamForm");
  const teamId = form.dataset.teamId;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  fetch(`http://localhost:8888/sports-league-app/backend/api/teams/${teamId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showMessage("Team updated successfully", "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editTeamModal")
        );
        modal.hide();
        loadTeams();
      } else {
        showMessage("Error updating team: " + result.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showMessage("Error updating team", "error");
    });
}
