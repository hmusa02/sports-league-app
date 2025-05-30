let Utils = {
  parseJwt: function (token) {
    if (!token) return null;
    try {
      const payload = token.split(".")[1];
      const decoded = atob(payload);
      return JSON.parse(decoded);
    } catch (e) {
      console.error("Invalid JWT token", e);
      return null;
    }
  },

  getCurrentUser: function () {
    const token = localStorage.getItem("user_token");
    if (!token) return null;

    const decoded = this.parseJwt(token);
    return decoded ? decoded.user : null;
  },

  isLoggedIn: function () {
    const token = localStorage.getItem("user_token");
    if (!token) return false;

    const decoded = this.parseJwt(token);
    if (!decoded) return false;

    const currentTime = Date.now() / 1000;
    return decoded.exp > currentTime;
  },

  logout: function () {
    localStorage.removeItem("user_token");
    window.location.href = "login.html";
  },

  redirectIfNotLoggedIn: function () {
    if (!this.isLoggedIn()) {
      window.location.href = "login.html";
    }
  },

  showMessage: function (message, type = "info") {
    alert(message);
  },
};
