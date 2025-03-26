function loadPage(page) {
  fetch(`views/${page}.html`)
    .then((response) => response.text())
    .then((html) => {
      document.getElementById("content").innerHTML = html;
    })
    .catch((err) => console.error(err));
}

window.onload = () => {
  loadPage("home");
};
