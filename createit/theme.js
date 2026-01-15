// Load saved theme before page renders
function initTheme() {
    const savedTheme = localStorage.getItem("theme") || "light";
    document.body.className = savedTheme;
}

// Initialize immediately
initTheme();

// Set up toggle button when DOM is ready
function setupThemeToggle() {
    const body = document.body;
    const toggleBtn = document.getElementById("themeToggle");
    
    if (!toggleBtn) return;
    
    function updateThemeButton() {
        toggleBtn.textContent = body.classList.contains("dark") ? "Licht" : "Donker";
    }
    
    toggleBtn.addEventListener("click", () => {
        const isDark = body.classList.contains("dark");
        body.className = isDark ? "light" : "dark";
        localStorage.setItem("theme", body.className);
        localStorage.setItem("theme", element.className);
        updateThemeButton();
    });
    
    updateThemeButton();
}

// Wait for DOM to be ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupThemeToggle);
} else {
    setupThemeToggle();
}
