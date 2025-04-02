document.addEventListener('DOMContentLoaded', function () {
    // Toggle mobile navigation
    const navToggle = document.createElement('div');
    navToggle.className = 'nav-toggle';
    navToggle.innerHTML = '<span></span><span></span><span></span>';

    const nav = document.querySelector('nav');
    if (nav && window.innerWidth < 768) {
        nav.parentNode.insertBefore(navToggle, nav);
        nav.classList.add('collapsed');

        navToggle.addEventListener('click', function () {
            nav.classList.toggle('collapsed');
        });
    }

    // Form validations
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    const formGroup = input.closest('.form-group');

                    // Remove existing error messages
                    const existingError = formGroup.querySelector('.error-message');
                    if (existingError) existingError.remove();

                    // Create error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'This field is required';
                    errorMsg.style.color = '#e74c3c';
                    errorMsg.style.fontSize = '0.8rem';
                    errorMsg.style.marginTop = '0.25rem';

                    formGroup.appendChild(errorMsg);

                    // Add error styling
                    input.style.borderColor = '#e74c3c';

                    // Remove error styling on input
                    input.addEventListener('input', function () {
                        if (input.value.trim()) {
                            input.style.borderColor = '';
                            const errorMsg = formGroup.querySelector('.error-message');
                            if (errorMsg) errorMsg.remove();
                        }
                    });
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Alert dismissal
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Create dismiss button
        const dismissBtn = document.createElement('button');
        dismissBtn.innerHTML = '&times;';
        dismissBtn.className = 'alert-dismiss';
        dismissBtn.style.float = 'right';
        dismissBtn.style.background = 'none';
        dismissBtn.style.border = 'none';
        dismissBtn.style.fontSize = '1.2rem';
        dismissBtn.style.cursor = 'pointer';

        alert.insertBefore(dismissBtn, alert.firstChild);

        dismissBtn.addEventListener('click', function () {
            alert.style.display = 'none';
        });

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });

    // Confirm deletes
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});