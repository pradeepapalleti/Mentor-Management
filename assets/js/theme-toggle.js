// Theme Toggle Functionality
(function() {
    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    // Create theme toggle button
    function createThemeToggle() {
        // Check if toggle already exists
        if (document.querySelector('.theme-toggle')) return;
        
        const toggle = document.createElement('div');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = `
            <i class="fas fa-${currentTheme === 'dark' ? 'sun' : 'moon'}"></i>
            <span>${currentTheme === 'dark' ? 'Light' : 'Dark'} Mode</span>
        `;
        
        toggle.addEventListener('click', toggleTheme);
        document.body.appendChild(toggle);
    }
    
    // Toggle theme function
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update toggle button
        const toggle = document.querySelector('.theme-toggle');
        if (toggle) {
            toggle.innerHTML = `
                <i class="fas fa-${newTheme === 'dark' ? 'sun' : 'moon'}"></i>
                <span>${newTheme === 'dark' ? 'Light' : 'Dark'} Mode</span>
            `;
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createThemeToggle);
    } else {
        createThemeToggle();
    }
})();
