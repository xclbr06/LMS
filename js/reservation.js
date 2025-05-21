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
