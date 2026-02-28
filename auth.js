document.addEventListener('DOMContentLoaded', function () {
    // Password Toggle
    const toggleButtons = document.querySelectorAll('.toggle-pw');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Simple Form Validation (Example)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const email = document.getElementById('loginEmail').value;
            const pass = document.getElementById('loginPassword').value;
            let valid = true;

            if (!email) {
                document.getElementById('emailError').textContent = 'Email is required';
                valid = false;
            } else {
                document.getElementById('emailError').textContent = '';
            }

            if (!pass) {
                document.getElementById('passwordError').textContent = 'Password is required';
                valid = false;
            } else {
                document.getElementById('passwordError').textContent = '';
            }

            if (!valid) e.preventDefault();
        });
    }
});
