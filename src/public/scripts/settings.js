document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll(".settings-link");
    const panels = document.querySelectorAll(".settings-panel");

    function showSection(sectionId, updateUrl = true) {
        const target = document.getElementById(sectionId);

        if (
            !target ||
            !target.classList.contains("settings-panel")
        ) {
            return;
        }

        panels.forEach((panel) => {
            const isActive = panel === target;

            panel.hidden = !isActive;
            panel.classList.toggle("active", isActive);

            if (isActive) {
                panel.scrollTop = 0;
            }
        });

        links.forEach((link) => {
            const isActive = link.dataset.section === sectionId;

            link.classList.toggle("active", isActive);

            if (isActive) {
                link.setAttribute("aria-current", "page");
            } else {
                link.removeAttribute("aria-current");
            }
        });

        if (updateUrl) {
            history.replaceState(null, "", `#${sectionId}`);
        }
    }

    links.forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            showSection(link.dataset.section);
        });
    });

    const initialSection = window.location.hash.substring(1);
    const initialTarget = document.getElementById(initialSection);

    if (
        initialTarget &&
        initialTarget.classList.contains("settings-panel")
    ) {
        showSection(initialSection, false);
    } else {
        showSection("profile", false);
    }

    window.addEventListener("hashchange", () => {
        const sectionId = window.location.hash.substring(1);

        if (sectionId) {
            showSection(sectionId, false);
        }
    });
});

document.querySelectorAll('[data-notification]').forEach(checkbox => {
    checkbox.addEventListener('change', async function () {
        const previousValue = !this.checked;

        const data = new URLSearchParams({
            type: this.dataset.notification,
            value: this.checked ? '1' : '0'
        });

        this.disabled = true;

        try {
            const response = await fetch('/settings/updateNotification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: data
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error('Échec de la mise à jour');
            }
        } catch (error) {
            // En cas d'erreur, on rétablit l'état précédent.
            this.checked = previousValue;
            alert('Impossible de modifier cette préférence.');
        } finally {
            this.disabled = false;
        }
    });
});