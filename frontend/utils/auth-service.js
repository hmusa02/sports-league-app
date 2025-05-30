let AuthService = {
  init: function () {
    const token = localStorage.getItem("user_token");
    if (token && Utils.isLoggedIn()) {
      window.location.href = "index.html";
    }
  },

  login: function (credentials, callback, errorCallback) {
    $.ajax({
      url: Constants.PROJECT_BASE_URL + "api/auth/login",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(credentials),
      success: function (response) {
        if (response.success) {
          localStorage.setItem("user_token", response.data.token);
          if (callback) callback(response);
        } else {
          if (errorCallback) errorCallback(response.message);
        }
      },
      error: function (xhr) {
        const message = xhr.responseJSON
          ? xhr.responseJSON.message
          : "Login failed";
        if (errorCallback) errorCallback(message);
      },
    });
  },

  register: function (userData, callback, errorCallback) {
    $.ajax({
      url: Constants.PROJECT_BASE_URL + "api/auth/register",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(userData),
      success: function (response) {
        if (response.success) {
          if (callback) callback(response);
        } else {
          if (errorCallback) errorCallback(response.message);
        }
      },
      error: function (xhr) {
        const message = xhr.responseJSON
          ? xhr.responseJSON.message
          : "Registration failed";
        if (errorCallback) errorCallback(message);
      },
    });
  },

  logout: function () {
    localStorage.removeItem("user_token");
    window.location.href = "login.html";
  },
};
