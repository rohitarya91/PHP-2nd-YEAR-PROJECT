document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-pw');

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');

            if (!input || !icon) {
                return;
            }

            const nextType = input.type === 'password' ? 'text' : 'password';
            input.type = nextType;
            icon.classList.toggle('fa-eye', nextType === 'password');
            icon.classList.toggle('fa-eye-slash', nextType === 'text');
        });
    });

    function setHint(id, message) {
        const hint = document.getElementById(id);
        if (!hint) {
            return;
        }

        hint.textContent = message;
        hint.classList.toggle('is-visible', message !== '');
    }

    function bindValidation(formId, fields) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            let isValid = true;

            fields.forEach(function (field) {
                const input = document.getElementById(field.inputId);
                if (!input) {
                    return;
                }

                const value = input.value.trim();
                let message = '';

                if (field.required && value === '') {
                    message = field.emptyMessage;
                } else if (field.type === 'email' && value !== '') {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(value)) {
                        message = 'Enter a valid email address.';
                    }
                } else if (field.type === 'password' && value !== '' && field.minLength && value.length < field.minLength) {
                    message = `Password must be at least ${field.minLength} characters.`;
                }

                setHint(field.errorId, message);

                if (message !== '') {
                    isValid = false;
                }
            });

            const submitButton = form.querySelector('button[type="submit"]');
            if (!isValid) {
                event.preventDefault();
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
            }
        });
    }

    bindValidation('loginForm', [
        {
            inputId: 'loginEmail',
            errorId: 'loginEmailError',
            required: true,
            emptyMessage: 'Email, user id, or phone is required.'
        },
        {
            inputId: 'loginPassword',
            errorId: 'loginPasswordError',
            required: true,
            type: 'password',
            emptyMessage: 'Password is required.'
        }
    ]);

    bindValidation('signupForm', [
        {
            inputId: 'signupName',
            errorId: 'signupNameError',
            required: true,
            emptyMessage: 'Full name is required.'
        },
        {
            inputId: 'signupEmail',
            errorId: 'signupEmailError',
            required: true,
            type: 'email',
            emptyMessage: 'Email address is required.'
        },
        {
            inputId: 'signupPassword',
            errorId: 'signupPasswordError',
            required: true,
            type: 'password',
            minLength: 8,
            emptyMessage: 'Password is required.'
        },
        {
            inputId: 'signupConfirmPassword',
            errorId: 'signupConfirmPasswordError',
            required: true,
            type: 'confirm-password',
            emptyMessage: 'Please confirm your password.'
        }
    ]);

    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function (event) {
            const password = document.getElementById('signupPassword');
            const confirmPassword = document.getElementById('signupConfirmPassword');

            if (!password || !confirmPassword) {
                return;
            }

            if (password.value !== confirmPassword.value) {
                setHint('signupConfirmPasswordError', 'Passwords do not match.');
                event.preventDefault();
            } else {
                setHint('signupConfirmPasswordError', '');
            }
        });
    }
});
