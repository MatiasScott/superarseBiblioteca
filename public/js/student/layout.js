let studentInactivityTimer = null;

function toggleStudentSidebar() {
    document.getElementById('studentSidebar')?.classList.toggle('open');
    document.getElementById('studentSidebarOverlay')?.classList.toggle('hidden');
}

function closeStudentSidebar() {
    document.getElementById('studentSidebar')?.classList.remove('open');
    document.getElementById('studentSidebarOverlay')?.classList.add('hidden');
}

function resetStudentInactivityTimer() {
    if (studentInactivityTimer) clearTimeout(studentInactivityTimer);

    studentInactivityTimer = setTimeout(() => {
        Swal.fire({
            icon: 'warning',
            title: 'Sesion expirada',
            text: 'Tu sesion finalizo por inactividad.',
            confirmButtonText: 'Iniciar sesion',
            allowOutsideClick: false
        }).then(() => {
            window.location.replace(`${BASE_URL}/logout`);
        });
    }, 5 * 60 * 1000);
}

['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, resetStudentInactivityTimer, true);
});

document.addEventListener('DOMContentLoaded', () => {
    resetStudentInactivityTimer();
});
