class StatisticsService {
  constructor() {
    // Event types for validation
    this.validEventTypes = [
      "goal",
      "assist",
      "yellow_card",
      "red_card",
      "substitution",
    ];
  }

  // Get all statistics
  async getAll() {
    try {
      return await APIService.get("/statistics");
    } catch (error) {
      console.error("Error loading statistics:", error);
      throw error;
    }
  }

  // Get top scorers
  async getTopScorers() {
    try {
      return await APIService.get("/statistics/top-scorers", false);
    } catch (error) {
      console.error("Error loading top scorers:", error);
      throw error;
    }
  }

  // Get player summary
  async getPlayerSummary(playerId) {
    try {
      return await APIService.get(
        `/statistics/player/${playerId}/summary`,
        false
      );
    } catch (error) {
      console.error("Error loading player summary:", error);
      throw error;
    }
  }

  // Create new statistic
  async create(data) {
    try {
      // Validate data
      const validation = this.validateStatisticData(data);
      if (!validation.isValid) {
        throw new Error(validation.message);
      }

      // Sanitize data
      const sanitizedData = this.sanitizeStatisticData(data);

      return await APIService.post("/statistics", sanitizedData);
    } catch (error) {
      console.error("Error creating statistic:", error);
      throw error;
    }
  }

  // Update statistic
  async update(id, data) {
    try {
      // Validate data
      const validation = this.validateStatisticData(data);
      if (!validation.isValid) {
        throw new Error(validation.message);
      }

      // Sanitize data
      const sanitizedData = this.sanitizeStatisticData(data);

      return await APIService.put(`/statistics/${id}`, sanitizedData);
    } catch (error) {
      console.error("Error updating statistic:", error);
      throw error;
    }
  }

  // Delete statistic
  async delete(id) {
    try {
      if (!id || isNaN(id)) {
        throw new Error("Invalid statistic ID");
      }

      return await APIService.delete(`/statistics/${id}`);
    } catch (error) {
      console.error("Error deleting statistic:", error);
      throw error;
    }
  }

  // Get statistic by ID
  async getById(id) {
    try {
      if (!id || isNaN(id)) {
        throw new Error("Invalid statistic ID");
      }

      return await APIService.get(`/statistics/${id}`);
    } catch (error) {
      console.error("Error loading statistic:", error);
      throw error;
    }
  }

  // Validate statistic data
  validateStatisticData(data) {
    const errors = [];

    // Match ID validation
    if (!data.match_id || isNaN(data.match_id)) {
      errors.push("Please select a valid match");
    }

    // Player ID validation
    if (!data.player_id || isNaN(data.player_id)) {
      errors.push("Please select a valid player");
    }

    // Event type validation
    if (!data.event_type || !this.validEventTypes.includes(data.event_type)) {
      errors.push("Please select a valid event type");
    }

    // Minute validation
    if (
      !data.minute ||
      isNaN(data.minute) ||
      data.minute < 1 ||
      data.minute > 120
    ) {
      errors.push("Minute must be between 1 and 120");
    }

    return {
      isValid: errors.length === 0,
      message: errors.join("; "),
    };
  }

  // Sanitize statistic data
  sanitizeStatisticData(data) {
    return {
      match_id: parseInt(data.match_id),
      player_id: parseInt(data.player_id),
      event_type: AuthService.sanitizeInput(data.event_type),
      minute: parseInt(data.minute),
    };
  }

  // Get matches for dropdown
  async getMatches() {
    try {
      return await APIService.get("/matches", false);
    } catch (error) {
      console.error("Error loading matches:", error);
      throw error;
    }
  }

  // Get players for dropdown
  async getPlayers() {
    try {
      return await APIService.get("/players", false);
    } catch (error) {
      console.error("Error loading players:", error);
      throw error;
    }
  }

  // Format statistics for display
  formatStatisticsForDisplay(statistics) {
    return statistics.map((stat) => ({
      ...stat,
      match_name: stat.match_name || "Unknown Match",
      player_name: stat.player_name || "Unknown Player",
      event_type_display: this.formatEventType(stat.event_type),
      minute_display: `${stat.minute}'`,
    }));
  }

  // Format event type for display
  formatEventType(eventType) {
    const eventTypeMap = {
      goal: "Goal",
      assist: "Assist",
      yellow_card: "Yellow Card",
      red_card: "Red Card",
      substitution: "Substitution",
    };

    return eventTypeMap[eventType] || eventType;
  }

  // Format top scorers for display
  formatTopScorersForDisplay(scorers) {
    return scorers.map((scorer) => ({
      ...scorer,
      player_name: `${scorer.first_name} ${scorer.last_name}`,
      goals_display: scorer.goals || 0,
    }));
  }
}

// Create singleton instance
const statisticsService = new StatisticsService();

// Export for use in other files
window.StatisticsService = statisticsService;
