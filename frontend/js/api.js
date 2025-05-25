// API Helper functions
var API = {
  // Generic GET request
  get: function (endpoint, callback, errorCallback) {
    $.ajax({
      url: Constants.get_url(endpoint),
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (xhr, status, error) {
        console.error("API GET Error:", error);
        if (errorCallback) errorCallback(xhr, status, error);
      },
    });
  },

  // Generic POST request
  post: function (endpoint, data, callback, errorCallback) {
    $.ajax({
      url: Constants.get_url(endpoint),
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      dataType: "json",
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (xhr, status, error) {
        console.error("API POST Error:", error);
        if (errorCallback) errorCallback(xhr, status, error);
      },
    });
  },

  // Generic PUT request
  put: function (endpoint, data, callback, errorCallback) {
    $.ajax({
      url: Constants.get_url(endpoint),
      type: "PUT",
      contentType: "application/json",
      data: JSON.stringify(data),
      dataType: "json",
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (xhr, status, error) {
        console.error("API PUT Error:", error);
        if (errorCallback) errorCallback(xhr, status, error);
      },
    });
  },

  // Generic DELETE request
  delete: function (endpoint, callback, errorCallback) {
    $.ajax({
      url: Constants.get_url(endpoint),
      type: "DELETE",
      dataType: "json",
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (xhr, status, error) {
        console.error("API DELETE Error:", error);
        if (errorCallback) errorCallback(xhr, status, error);
      },
    });
  },

  // Health check
  healthCheck: function (callback) {
    this.get(Constants.endpoints.health, callback);
  },

  // User registration
  register: function (userData, callback, errorCallback) {
    this.post(Constants.endpoints.register, userData, callback, errorCallback);
  },

  // User login
  login: function (loginData, callback, errorCallback) {
    this.post(Constants.endpoints.login, loginData, callback, errorCallback);
  },

  // User logout
  logout: function (callback, errorCallback) {
    this.post(Constants.endpoints.logout, {}, callback, errorCallback);
  },

  // Get current user
  getCurrentUser: function (callback, errorCallback) {
    this.get(Constants.endpoints.current_user, callback, errorCallback);
  },

  // Get all teams
  getTeams: function (callback, errorCallback) {
    this.get(Constants.endpoints.teams, callback, errorCallback);
  },

  // Get all players
  getPlayers: function (callback, errorCallback) {
    this.get(Constants.endpoints.players, callback, errorCallback);
  },

  // Get matches
  getMatches: function (callback, errorCallback) {
    this.get(Constants.endpoints.matches, callback, errorCallback);
  },

  // Get upcoming matches
  getUpcomingMatches: function (callback, errorCallback) {
    this.get(Constants.endpoints.upcoming_matches, callback, errorCallback);
  },

  // Get top scorers
  getTopScorers: function (callback, errorCallback) {
    this.get(Constants.endpoints.top_scorers, callback, errorCallback);
  },
};
