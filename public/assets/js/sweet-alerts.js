// Global SweetAlert2 Toast Configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Global Delete Confirmation Configuration
function showDeleteConfirmation(options) {
    return Swal.fire({
        title: options.title || 'Delete Confirmation',
        text: options.text || 'Are you sure you want to delete this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmButtonText || 'Yes, delete it!',
        cancelButtonText: options.cancelButtonText || 'Cancel'
    });
}

// Show Success/Error Toast
function showToast(status, message) {
    Toast.fire({
        icon: status,
        title: message
    });
}

// Handle Delete Action with Form Submit
function handleDelete(formId) {
    document.getElementById(formId).submit();
}

// Handle Delete Action with Route
function handleDeleteWithRoute(route) {
    window.location.href = route;
}
