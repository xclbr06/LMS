document.addEventListener('DOMContentLoaded', function () {
    // --- SEARCH & SORT LOGIC ---
    function compare(a, b, type, order) {
        // Handles different data type comparisons
        if (type === 'number') {
            a = parseFloat(a) || 0; b = parseFloat(b) || 0;
        } else if (type === 'date') {
            a = new Date(a); b = new Date(b);
        } else {
            a = (a || '').toString().toLowerCase();
            b = (b || '').toString().toLowerCase();
        }
        if (a < b) return order === 'asc' ? -1 : 1;
        if (a > b) return order === 'asc' ? 1 : -1;
        return 0;
    }

    function searchAndSortTable({tableId, searchId, sortFieldId, sortOrderId, fieldTypes, searchFields}) {
        // Implements dynamic table filtering and sorting
        const search = document.getElementById(searchId)?.value.toLowerCase() || '';
        const sortField = document.getElementById(sortFieldId)?.value || '';
        const sortOrder = document.getElementById(sortOrderId)?.value || 'asc';
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = Array.from(table.tBodies[0].rows);

        // Filter
        rows.forEach(row => {
            const text = searchFields.map(f => {
                const td = row.querySelector(`td[data-field="${f}"]`);
                return td ? (td.innerText || td.textContent).toLowerCase() : '';
            }).join(' ');
            row.style.display = text.includes(search) ? '' : 'none';
        });

        // Sort
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            let aVal = a.querySelector(`td[data-field="${sortField}"]`);
            let bVal = b.querySelector(`td[data-field="${sortField}"]`);
            aVal = aVal ? (aVal.innerText || aVal.textContent) : '';
            bVal = bVal ? (bVal.innerText || bVal.textContent) : '';
            let type = fieldTypes[sortField] || 'string';
            return compare(aVal, bVal, type, sortOrder);
        });
        visibleRows.forEach(row => table.tBodies[0].appendChild(row));
    }

    // --- Inventory ---
    function inventorySearchSort() {
        searchAndSortTable({
            tableId: 'inventoryTable',
            searchId: 'inventorySearch',
            sortFieldId: 'inventorySortField',
            sortOrderId: 'inventorySortOrder',
            fieldTypes: {id: 'number', title: 'string', author: 'string', category: 'string', status: 'string'},
            searchFields: ['id', 'title', 'author', 'category', 'status']
        });
    }
    if (document.getElementById('inventorySearch')) {
        document.getElementById('inventorySearch').addEventListener('input', inventorySearchSort);
    }
    if (document.getElementById('inventorySortField')) {
        document.getElementById('inventorySortField').addEventListener('change', inventorySearchSort);
    }
    if (document.getElementById('inventorySortOrder')) {
        document.getElementById('inventorySortOrder').addEventListener('change', inventorySearchSort);
    }
    if (document.getElementById('inventoryTable')) {
        inventorySearchSort();
    }

    // --- Categories ---
    function categorySearchSort() {
        // Implements category filtering and sorting
        searchAndSortTable({
            tableId: 'categoriesTable',
            searchId: 'categorySearch',
            sortFieldId: 'categorySortOrder', // Only order, so use as field too
            sortOrderId: 'categorySortOrder',
            fieldTypes: {category: 'string'},
            searchFields: ['category']
        });
    }
    if (document.getElementById('categorySearch')) {
        document.getElementById('categorySearch').addEventListener('input', categorySearchSort);
    }
    if (document.getElementById('categorySortOrder')) {
        document.getElementById('categorySortOrder').addEventListener('change', categorySearchSort);
    }
    if (document.getElementById('categoriesTable')) {
        categorySearchSort();
    }

    // --- Users ---
    function userSearchSort() {
        // Implements user filtering and sorting
        searchAndSortTable({
            tableId: 'usersTable',
            searchId: 'userSearch',
            sortFieldId: 'userSortField',
            sortOrderId: 'userSortOrder',
            fieldTypes: {first_name: 'string', last_name: 'string', email: 'string', role: 'string'},
            searchFields: ['first_name', 'middle_name', 'last_name', 'email', 'student_id', 'phone', 'role']
        });
    }
    if (document.getElementById('userSearch')) {
        document.getElementById('userSearch').addEventListener('input', userSearchSort);
    }
    if (document.getElementById('userSortField')) {
        document.getElementById('userSortField').addEventListener('change', userSearchSort);
    }
    if (document.getElementById('userSortOrder')) {
        document.getElementById('userSortOrder').addEventListener('change', userSearchSort);
    }
    if (document.getElementById('usersTable')) {
        userSearchSort();
    }

    // --- Reservations ---
    function reservationSearchSort() {
        // Implements reservation filtering and sorting
        searchAndSortTable({
            tableId: 'reservationsTable',
            searchId: 'reservationSearch',
            sortFieldId: 'reservationSortField',
            sortOrderId: 'reservationSortOrder',
            fieldTypes: {user: 'string', book: 'string', due_date: 'date', status: 'string'},
            searchFields: ['id', 'user', 'book', 'reserved_at', 'due_date', 'status']
        });
    }
    if (document.getElementById('reservationSearch')) {
        document.getElementById('reservationSearch').addEventListener('input', reservationSearchSort);
    }
    if (document.getElementById('reservationSortField')) {
        document.getElementById('reservationSortField').addEventListener('change', reservationSearchSort);
    }
    if (document.getElementById('reservationSortOrder')) {
        document.getElementById('reservationSortOrder').addEventListener('change', reservationSearchSort);
    }
    if (document.getElementById('reservationsTable')) {
        reservationSearchSort();
    }

    // Reservation date handling
    function setupReservationDates() {
        const borrowStartDate = document.getElementById('admin_borrow_start_date');
        const dueDate = document.getElementById('admin_due_date');
        const userSelect = document.querySelector('select[name="user_id"]');
        
        if (!borrowStartDate || !dueDate || !userSelect) return;

        // Set initial borrow period based on selected user's role
        function updateBorrowPeriod() {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userRole = selectedOption?.getAttribute('data-role');
            return userRole === 'teacher' ? 30 : 14;
        }

        // Set borrow start date constraints
        function setBorrowStartConstraints() {
            const today = new Date();
            const maxStart = new Date();
            maxStart.setDate(today.getDate() + 7);
            
            borrowStartDate.min = today.toISOString().split('T')[0];
            borrowStartDate.max = maxStart.toISOString().split('T')[0];
            
            // Set to today if no date is selected or date is invalid
            if (!borrowStartDate.value || new Date(borrowStartDate.value) < today) {
                borrowStartDate.value = today.toISOString().split('T')[0];
            }
        }

        // Update return date constraints
        function updateDueDateLimits() {
            if (!borrowStartDate.value) return;

            const borrowPeriod = updateBorrowPeriod();
            const selectedStart = new Date(borrowStartDate.value);
            
            // Set minimum return date (next day)
            const minDue = new Date(selectedStart);
            minDue.setDate(selectedStart.getDate() + 1);
            
            // Set maximum return date (borrowPeriod days later)
            const maxDue = new Date(selectedStart);
            maxDue.setDate(selectedStart.getDate() + borrowPeriod);
            
            // Update return date constraints
            dueDate.min = minDue.toISOString().split('T')[0];
            dueDate.max = maxDue.toISOString().split('T')[0];
            
            // Auto-set to minimum date if current value is invalid
            if (!dueDate.value || 
                new Date(dueDate.value) < minDue || 
                new Date(dueDate.value) > maxDue) {
                dueDate.value = minDue.toISOString().split('T')[0];
            }
        }

        // Event Listeners
        userSelect.addEventListener('change', function() {
            updateDueDateLimits();
        });

        borrowStartDate.addEventListener('change', function() {
            updateDueDateLimits();
        });

        // Initialize on modal show
        const modal = document.getElementById('addReservationModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                setBorrowStartConstraints();
                updateDueDateLimits();
            });
        }

        // Initial setup
        setBorrowStartConstraints();
        updateDueDateLimits();
    }

    setupReservationDates();

    // Re-initialize dates when modal is shown
    const addReservationModal = document.getElementById('addReservationModal');
    if (addReservationModal) {
        addReservationModal.addEventListener('shown.bs.modal', setupReservationDates);
    }
});

// Modal Management Block
// Handles modal dialogs for forms
function closeModal(modalId) {
    // Manages modal window closing
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}