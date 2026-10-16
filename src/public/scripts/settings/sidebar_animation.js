
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