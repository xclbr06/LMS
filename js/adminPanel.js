document.addEventListener('DOMContentLoaded', function () {
    // --- SEARCH & SORT LOGIC ---
    function compare(a, b, type, order) {
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
    document.getElementById('inventorySearch').addEventListener('input', inventorySearchSort);
    document.getElementById('inventorySortField').addEventListener('change', inventorySearchSort);
    document.getElementById('inventorySortOrder').addEventListener('change', inventorySearchSort);
    inventorySearchSort();

    // --- Categories ---
    function categorySearchSort() {
        searchAndSortTable({
            tableId: 'categoriesTable',
            searchId: 'categorySearch',
            sortFieldId: 'categorySortOrder', // Only order, so use as field too
            sortOrderId: 'categorySortOrder',
            fieldTypes: {category: 'string'},
            searchFields: ['category']
        });
    }
    document.getElementById('categorySearch').addEventListener('input', categorySearchSort);
    document.getElementById('categorySortOrder').addEventListener('change', categorySearchSort);
    categorySearchSort();

    // --- Users ---
    function userSearchSort() {
        searchAndSortTable({
            tableId: 'usersTable',
            searchId: 'userSearch',
            sortFieldId: 'userSortField',
            sortOrderId: 'userSortOrder',
            fieldTypes: {first_name: 'string', last_name: 'string', email: 'string', role: 'string'},
            searchFields: ['first_name', 'middle_name', 'last_name', 'email', 'student_id', 'phone', 'role']
        });
    }
    document.getElementById('userSearch').addEventListener('input', userSearchSort);
    document.getElementById('userSortField').addEventListener('change', userSearchSort);
    document.getElementById('userSortOrder').addEventListener('change', userSearchSort);
    userSearchSort();

    // --- Reservations ---
    function reservationSearchSort() {
        searchAndSortTable({
            tableId: 'reservationsTable',
            searchId: 'reservationSearch',
            sortFieldId: 'reservationSortField',
            sortOrderId: 'reservationSortOrder',
            fieldTypes: {user: 'string', book: 'string', due_date: 'date', status: 'string'},
            searchFields: ['id', 'user', 'book', 'reserved_at', 'due_date', 'status']
        });
    }
    document.getElementById('reservationSearch').addEventListener('input', reservationSearchSort);
    document.getElementById('reservationSortField').addEventListener('change', reservationSearchSort);
    document.getElementById('reservationSortOrder').addEventListener('change', reservationSearchSort);
    reservationSearchSort();
});

// Modal close function
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
