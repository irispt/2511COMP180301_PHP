/**
 * Samsung Style Header Functions
 */

// Toggle search dropdown
function toggleSearch() {
    const searchDropdown = document.getElementById('searchDropdown');
    const isVisible = searchDropdown.style.display === 'block';
    
    if (isVisible) {
        searchDropdown.style.display = 'none';
    } else {
        searchDropdown.style.display = 'block';
        // Focus on input
        const input = searchDropdown.querySelector('input');
        if (input) input.focus();
    }
}

// Close search when clicking outside
document.addEventListener('click', function(event) {
    const searchTool = document.querySelector('.tool-search');
    const searchDropdown = document.getElementById('searchDropdown');
    const accountTool = document.querySelector('.tool-account');
    const accountDropdown = document.querySelector('.account-dropdown');
    
    // Close search if clicking outside
    if (searchTool && searchDropdown && !searchTool.contains(event.target)) {
        searchDropdown.style.display = 'none';
    }
    
    // Close account dropdown if clicking outside
    if (accountTool && accountDropdown && !accountTool.contains(event.target)) {
        accountDropdown.classList.remove('show');
    }
});

// Active navigation highlighting
document.addEventListener('DOMContentLoaded', function() {
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (currentUrl.includes(link.getAttribute('href')) && link.getAttribute('href') !== '#') {
            link.classList.add('active');
        }
    });
    
    // If on homepage
    if (currentUrl.endsWith('index.php') || currentUrl.endsWith('/')) {
        const homeLink = document.querySelector('.nav-link[href="index.php"]');
        if (homeLink) homeLink.classList.add('active');
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});
