document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('due_date_picker');
    if (dateInput) {
        // Get role and period from data attributes if present
        let borrowPeriod = 14; // default for student
        if (dateInput.dataset.period) {
            borrowPeriod = parseInt(dateInput.dataset.period, 10);
        }
        const today = new Date();
        // min: tomorrow
        const minDateObj = new Date(today);
        minDateObj.setDate(today.getDate() + 1);
        const minDate = minDateObj.toISOString().split('T')[0];
        // max: today + borrowPeriod
        const maxDateObj = new Date(today);
        maxDateObj.setDate(today.getDate() + borrowPeriod);
        const maxDate = maxDateObj.toISOString().split('T')[0];
        dateInput.setAttribute('min', minDate);
        dateInput.setAttribute('max', maxDate);
    }
});

// reservation.js

document.addEventListener('DOMContentLoaded', function() {
    var borrowStartInput = document.getElementById('borrow_start_date');
    var dueDateInput = document.getElementById('due_date');

    if (borrowStartInput && dueDateInput) {
        function updateDueDateLimits() {
            var borrowStart = borrowStartInput.value;
            if (!borrowStart) return;

            // Get borrow period from a data attribute or set default (should match PHP)
            var borrowPeriod = 14; // Default, can be overridden below
            if (window.borrowPeriod) borrowPeriod = window.borrowPeriod;

            // Min due date: 1 day after borrow start
            var minDue = new Date(borrowStart);
            minDue.setDate(minDue.getDate() + 1);
            var minDueStr = minDue.toISOString().split('T')[0];

            // Max due date: borrowPeriod days after borrow start
            var maxDue = new Date(borrowStart);
            maxDue.setDate(maxDue.getDate() + borrowPeriod);
            var maxDueStr = maxDue.toISOString().split('T')[0];

            dueDateInput.min = minDueStr;
            dueDateInput.max = maxDueStr;

            // If current due date is out of range, reset it
            if (dueDateInput.value < minDueStr || dueDateInput.value > maxDueStr) {
                dueDateInput.value = minDueStr;
            }
        }

        // Optionally, set borrowPeriod from a global JS variable (set in reservation.html)
        if (window.borrowPeriod) {
            updateDueDateLimits();
        }

        borrowStartInput.addEventListener('change', updateDueDateLimits);

        // On page load, trigger once
        updateDueDateLimits();
    }
});