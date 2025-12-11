const body = document.body;
const toggleBtn = document.getElementById("themeToggle");

function updateThemeButton() {
    toggleBtn.textContent = body.classList.contains("dark") ? "Licht" : "Donker";
}

toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark");
    body.classList.toggle("light");
    updateThemeButton();
});

updateThemeButton();
