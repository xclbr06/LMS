document.addEventListener('DOMContentLoaded', function() {
    // Open modals
    document.getElementById('openAddBookModal').onclick = function() {
        document.getElementById('addBookModal').style.display = 'flex';
    };
    document.getElementById('openAddUserModal').onclick = function() {
        document.getElementById('addUserModal').style.display = 'flex';
    };
    document.getElementById('openAddCategoryModal').onclick = function() {
        document.getElementById('addCategoryModal').style.display = 'flex';
    };

    // Close modals on background click
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
});

// Close modal function for close button
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
