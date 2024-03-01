// Function to toggle the dark mode and update the button icon and text
function toggleDarkMode() {
    var isDarkMode = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');

    var themeToggleBtn = document.getElementById('theme-toggle');
    themeToggleBtn.innerHTML = isDarkMode 
        ? '<i class="fa-solid fa-sun"></i>' 
        : '<i class="fa-solid fa-moon"></i>';
}

// Event listener for the button
document.getElementById('theme-toggle').addEventListener('click', toggleDarkMode);

// Check for saved user preference, if any, when the page loads
window.onload = function() {
    var themeToggleBtn = document.getElementById('theme-toggle');
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        themeToggleBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
    } else {
        themeToggleBtn.innerHTML = '<i class="fa-solid fa-moon"></i>';
    }
};