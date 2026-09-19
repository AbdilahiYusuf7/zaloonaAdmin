document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.app-shell');
    const menuToggle = document.querySelector('.app-topbar__menu-toggle');
    const backdrop = document.querySelector('.app-sidebar-backdrop');

    if (shell && menuToggle) {
        menuToggle.addEventListener('click', () => {
            shell.classList.toggle('is-nav-open');
        });
    }

    if (shell && backdrop) {
        backdrop.addEventListener('click', () => {
            shell.classList.remove('is-nav-open');
        });
    }

    const openModal = (modal) => {
        modal.classList.add('is-open');
    };

    const closeModal = (modal) => {
        modal.classList.remove('is-open');
    };

    document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
        const modal = document.querySelector(trigger.getAttribute('data-modal-target'));

        if (modal) {
            trigger.addEventListener('click', () => openModal(modal));
        }
    });

    document.querySelectorAll('.modal-overlay').forEach((modal) => {
        modal.querySelectorAll('[data-modal-close]').forEach((closer) => {
            closer.addEventListener('click', () => closeModal(modal));
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.is-open').forEach(closeModal);
        }
    });

    document.querySelectorAll('[data-confirm-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modal = document.querySelector(trigger.getAttribute('data-confirm-trigger'));

            if (!modal) {
                return;
            }

            const idField = modal.querySelector('[data-confirm-id-field]');
            const nameField = modal.querySelector('[data-confirm-name-field]');

            if (idField) {
                idField.value = trigger.getAttribute('data-salon-id') || '';
            }

            if (nameField) {
                nameField.textContent = trigger.getAttribute('data-salon-name') || 'this salon';
            }

            openModal(modal);
        });
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const dismiss = () => {
            toast.classList.remove('is-visible');
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 200);
        };

        requestAnimationFrame(() => toast.classList.add('is-visible'));
        const timer = setTimeout(dismiss, 5000);

        const closeButton = toast.querySelector('[data-toast-close]');

        if (closeButton) {
            closeButton.addEventListener('click', () => {
                clearTimeout(timer);
                dismiss();
            });
        }
    });

    document.querySelectorAll('[data-file-upload-input]').forEach((input) => {
        const label = input.parentElement.querySelector('[data-file-upload-text]');
        const defaultText = label ? label.textContent : '';

        input.addEventListener('change', () => {
            if (label) {
                label.textContent = input.files.length > 0 ? input.files[0].name : defaultText;
            }
        });
    });
});
