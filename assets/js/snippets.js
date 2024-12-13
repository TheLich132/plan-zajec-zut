const toastTrigger = document.getElementById('liveToastBtn')
const toastLiveExample = document.getElementById('liveToast')

if (toastTrigger) {
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
    toastTrigger.addEventListener('click', () => {
        toastBootstrap.show()
    })
}

document.addEventListener('DOMContentLoaded', function () {
    const scheduleName = document.getElementById('scheduleName');
    const firstScheduleForm = document.getElementById('firstScheduleForm');
    const secondScheduleForm = document.getElementById('secondScheduleForm');
    const switchScheduleRight = document.getElementById('switchScheduleRight');
    const switchScheduleLeft = document.getElementById('switchScheduleLeft');

    let currentForm = 'first';

    function switchForm(nextForm) {
        if (currentForm === nextForm) return;

        if (nextForm === 'first') {
            firstScheduleForm.classList.add('active-form');
            firstScheduleForm.classList.remove('inactive-form');
            secondScheduleForm.classList.add('inactive-form');
            secondScheduleForm.classList.remove('active-form');
            currentForm = 'first';
            switchScheduleLeft.disabled = true;
            switchScheduleRight.disabled = false;
            scheduleName.textContent = 'Plan 1';
        } else if (nextForm === 'second') {
            firstScheduleForm.classList.add('inactive-form');
            firstScheduleForm.classList.remove('active-form');
            secondScheduleForm.classList.add('active-form');
            secondScheduleForm.classList.remove('inactive-form');
            currentForm = 'second';
            switchScheduleLeft.disabled = false;
            switchScheduleRight.disabled = true;
            scheduleName.textContent = 'Plan 2';
        }
    }

    switchScheduleRight.addEventListener('click', () => {
        switchForm('second');
    });

    switchScheduleLeft.addEventListener('click', () => {
        switchForm('first');
    });
});

// Event listener for color inputs
const colorInputs = document.querySelectorAll('input[type="color"]');

colorInputs.forEach((input) => {
    input.addEventListener('change', (event) => {
        const newValue = event.target.value;
        console.log(`Color of ${event.target.name} changed to: ${newValue}`);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleButton = document.getElementById('themeToggleButton');
    const systemDarkMode = window.matchMedia('(prefers-color-scheme: dark)');

    // Set or toggle dark mode
    const applyTheme = (isDark) => {
        document.body.classList.toggle('dark-mode', isDark);
        themeToggleButton.textContent = isDark ? '☀️' : '🌙';
    };

    // Initialize theme based on system preferences
    applyTheme(systemDarkMode.matches);

    // Update theme based on system preferences
    systemDarkMode.addEventListener('change', (e) => applyTheme(e.matches));

    // Button to toggle dark mode
    themeToggleButton.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-mode');
        themeToggleButton.textContent = isDark ? '☀️' : '🌙';
    });
});
