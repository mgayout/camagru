
import { sendSettingsRequest } from './utils.js';

document.querySelectorAll('[data-setting]').forEach(element => {
    const setting = element.dataset.setting;

    if (setting === 'password') {
        element.querySelector('button').addEventListener('click', async () => {
            try {
                await sendSettingsRequest('/settings/updatePassword', {
                    current_password: element.querySelector('[name="current_password"]').value,
                    new_password: element.querySelector('[name="new_password"]').value,
                    confirm_password: element.querySelector('[name="confirm_password"]').value
                });

                element.querySelectorAll('input').forEach(input => {
                    input.value = '';
                });
            } catch (error) {
                alert('Impossible de modifier le mot de passe.');
            }
        });

        return;
    }

    if (!element.matches('input')) {
        return;
    }

    element.dataset.previousValue = element.value;

    element.addEventListener('change', async function () {
        const previousValue = this.dataset.previousValue;

        this.disabled = true;

        try {
            await sendSettingsRequest(
                `/settings/update${setting.charAt(0).toUpperCase() + setting.slice(1)}`,
                { [setting]: this.value }
            );

            this.dataset.previousValue = this.value;
        } catch (error) {
            this.value = previousValue;
            alert('Impossible de modifier cette information.');
        } finally {
            this.disabled = false;
        }
    });
});

document.querySelectorAll('[data-notification]').forEach(checkbox => {
    checkbox.addEventListener('change', async function () {
        const previousValue = !this.checked;
        this.disabled = true;

        try {
            await sendSettingsRequest('/settings/updateNotification', {
                type: this.dataset.notification,
                value: this.checked ? '1' : '0'
            });
        } catch (error) {
            this.checked = previousValue;
            alert('Impossible de modifier cette préférence.');
        } finally {
            this.disabled = false;
        }
    });
});
