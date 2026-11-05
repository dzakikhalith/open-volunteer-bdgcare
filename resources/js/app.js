import 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach((alert) => {
        try {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            setTimeout(() => {
                try { bsAlert.close(); } catch (e) {}
            }, 4000);
        } catch (e) {}
    });
});

