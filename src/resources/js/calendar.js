/**
 * Laravel Livewire Calendar
 * 
 * This script adds additional functionality to the calendar component,
 * such as responsiveness and light/dark theme detection.
 */

document.addEventListener('livewire:initialized', () => {
    // Responsiveness configuration
    const setupResponsiveness = () => {
        const calendarComponents = document.querySelectorAll('.calendar-component');

        if (!calendarComponents.length) return;

        const checkViewport = () => {
            const isMobile = window.innerWidth < 640;
            const mobileView = getComputedStyle(document.documentElement).getPropertyValue('--calendar-mobile-view') || 'stack';

            calendarComponents.forEach(calendar => {
                const calendarWeeks = calendar.querySelector('.calendar-weeks');

                if (calendarWeeks) {
                    // Remove previous classes
                    calendarWeeks.classList.remove('stack', 'scroll');

                    // Add class based on configuration and viewport
                    if (isMobile) {
                        calendarWeeks.classList.add(mobileView);
                    }
                }
            });
        };

        // Check on load and resize
        checkViewport();
        window.addEventListener('resize', checkViewport);
    };

    // Detects theme changes (light/dark)
    const setupThemeDetection = () => {
        // Checks if dark theme is active
        const checkDarkMode = () => {
            return document.documentElement.classList.contains('dark');
        };

        // Updates CSS classes based on theme
        const updateThemeClasses = () => {
            const isDarkMode = checkDarkMode();
            document.body.classList.toggle('calendar-dark-mode', isDarkMode);
        };

        // Observes changes to the 'dark' class on the HTML tag
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'class') {
                    updateThemeClasses();
                }
            });
        });

        observer.observe(document.documentElement, { attributes: true });

        // Checks initial theme
        updateThemeClasses();
    };

    // Initializes functionalities
    setupResponsiveness();
    setupThemeDetection();
});