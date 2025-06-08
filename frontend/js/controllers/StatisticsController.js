class StatisticsController {
  constructor() {
    this.currentStatId = null;
    this.initialize();
  }

  // Initialize controller
  initialize() {
    this.setupEventListeners();
    this.loadInitialData();
  }

  // Setup event listeners
  setupEventListeners() {
    // Add statistic modal event
    const addModal = document.getElementById("addStatModal");
    if (addModal) {
      addModal.addEventListener("show.bs.modal", () => {
        this.loadFormData();
      });
    }

    // Add form validation
    ValidationService.addFormValidation("addStatForm");

    // Override form submit handler
    const addForm = document.getElementById("addStatForm");
    if (addForm) {
      addForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.handleAddStatistic();
      });
    }
  }

  // Load initial data
  async loadInitialData() {
    try {
      await this.loadStatistics();
      await this.loadTopScorers();
    } catch (error) {
      console.error("Error loading initial data:", error);
      this.showMessage("Error loading statistics", "error");
    }
  }

  // Load all statistics
  async loadStatistics() {
    try {
      if (!AuthService.isLoggedIn()) {
        this.showMessage("Please login first", "error");
        this.redirectToLogin();
        return;
      }

      const result = await StatisticsService.getAll();

      if (result.success) {
        const formattedStats = StatisticsService.formatStatisticsForDisplay(
          result.data
        );
        this.displayStatistics(formattedStats);
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error("Error loading statistics:", error);
      this.showMessage("Error loading statistics: " + error.message, "error");
    }
  }

  // Load top scorers
  async loadTopScorers() {
    try {
      const result = await StatisticsService.getTopScorers();

      if (result.success) {
        const formattedScorers = StatisticsService.formatTopScorersForDisplay(
          result.data
        );
        this.displayTopScorers(formattedScorers);
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error("Error loading top scorers:", error);
      // Don't show error for top scorers as it's not critical
    }
  }

  // Load form data (matches and players)
  async loadFormData() {
    try {
      const [matchesResult, playersResult] = await Promise.all([
        StatisticsService.getMatches(),
        StatisticsService.getPlayers(),
      ]);

      if (matchesResult.success) {
        this.populateMatchSelect(matchesResult.data);
      }

      if (playersResult.success) {
        this.populatePlayerSelect(playersResult.data);
      }
    } catch (error) {
      console.error("Error loading form data:", error);
      this.showMessage("Error loading form data", "error");
    }
  }

  // Handle add statistic
  async handleAddStatistic() {
    try {
      if (!AuthService.isLoggedIn()) {
        throw new Error("Please login first");
      }

      const formData = this.getFormData("addStatForm");
      const result = await StatisticsService.create(formData);

      if (result.success) {
        this.showMessage("Statistic added successfully", "success");
        this.closeModal("addStatModal");
        this.resetForm("addStatForm");

        // Reload data
        await this.loadStatistics();
        await this.loadTopScorers();
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error("Error adding statistic:", error);
      this.showMessage("Error adding statistic: " + error.message, "error");
    }
  }

  // Handle delete statistic
  async deleteStatistic(statId) {
    try {
      if (!AuthService.isAdmin()) {
        throw new Error("Only administrators can delete statistics");
      }

      if (!confirm("Are you sure you want to delete this statistic?")) {
        return;
      }

      const result = await StatisticsService.delete(statId);

      if (result.success) {
        this.showMessage("Statistic deleted successfully", "success");

        // Reload data
        await this.loadStatistics();
        await this.loadTopScorers();
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error("Error deleting statistic:", error);
      this.showMessage("Error deleting statistic: " + error.message, "error");
    }
  }

  // Display statistics in table
  displayStatistics(statistics) {
    const tbody = document.querySelector(".statistics-table tbody");
    if (!tbody) return;

    tbody.innerHTML = "";

    statistics.forEach((stat) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td>${stat.match_name}</td>
                <td>${stat.player_name}</td>
                <td>${stat.event_type_display}</td>
                <td>${stat.minute_display}</td>
                <td>
                    ${
                      AuthService.isAdmin()
                        ? `
                        <button class="btn btn-sm btn-danger" onclick="statisticsController.deleteStatistic(${stat.stat_id})">
                            Delete
                        </button>
                        <button class="btn btn-sm btn-info" onclick="statisticsController.showEditMessage()">
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
  displayTopScorers(scorers) {
    const tbody = document.getElementById("topScorersTable");
    if (!tbody) return;

    tbody.innerHTML = "";

    scorers.slice(0, 5).forEach((scorer) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td>${scorer.player_name}</td>
                <td>${scorer.goals_display}</td>
            `;
      tbody.appendChild(row);
    });
  }

  // Populate match select
  populateMatchSelect(matches) {
    const select = document.getElementById("statMatchSelect");
    if (!select) return;

    select.innerHTML = '<option value="">Select Match</option>';

    matches.forEach((match) => {
      const option = document.createElement("option");
      option.value = match.match_id;
      option.textContent = `${match.home_team_name} vs ${match.away_team_name} (${match.date_played})`;
      select.appendChild(option);
    });
  }

  // Populate player select
  populatePlayerSelect(players) {
    const select = document.getElementById("statPlayerSelect");
    if (!select) return;

    select.innerHTML = '<option value="">Select Player</option>';

    players.forEach((player) => {
      const option = document.createElement("option");
      option.value = player.player_id;
      option.textContent = `${player.first_name} ${player.last_name}`;
      select.appendChild(option);
    });
  }

  // Get form data
  getFormData(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    return Object.fromEntries(formData.entries());
  }

  // Reset form
  resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
      form.reset();

      // Clear validation classes
      const inputs = form.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => {
        ValidationService.clearFieldError(input);
      });
    }
  }

  // Close modal
  closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
      modal.hide();
    }
  }

  // Show edit message (placeholder)
  showEditMessage() {
    this.showMessage("Edit functionality coming soon", "info");
  }

  // Show message
  showMessage(message, type) {
    if (window.showMessage) {
      window.showMessage(message, type);
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }

  // Redirect to login
  redirectToLogin() {
    if (window.loadPage) {
      window.loadPage("login");
    }
  }

  // Refresh statistics
  async refreshStatistics() {
    await this.loadInitialData();
  }
}

// Create controller instance when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("topScorersTable")) {
    window.statisticsController = new StatisticsController();
  }
});
