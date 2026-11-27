const stickerGallery = document.getElementById("stickerGallery");
const stickerList = document.getElementById("stickerList");

const elementControls = document.getElementById("elementControls");
const elementSize = document.getElementById("elementSize");

const editor = document.getElementById("editor");
const photo = document.getElementById("photo");
const overlay = document.getElementById("overlay");

const publishPhoto = document.getElementById("publishPhoto");

const csrfToken = document.getElementById("csrf_token").value;

let overlayX = 0;
let overlayY = 0;

let isDragging = false;

let startX = 0;
let startY = 0;

let initialX = 0;
let initialY = 0;

let selectedSticker = null;
let stickersLoaded = false;

function resizeEditor() {
    if (!photo.naturalWidth || !photo.naturalHeight) return;

    const parent = editor.parentElement;

    const maxWidth = Math.min(parent.clientWidth, 800);
    const maxHeight = parent.clientHeight;

    const ratio = Math.min(
        maxWidth / photo.naturalWidth,
        maxHeight / photo.naturalHeight
    );

    editor.style.width = `${photo.naturalWidth * ratio}px`;
    editor.style.height = `${photo.naturalHeight * ratio}px`;
}

async function loadStickers() {
    try {
        const response = await fetch("/camagru/stickers");

        if (!response.ok) {
            throw new Error("Erreur HTTP : " + response.status);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(
                result.message || "Impossible de charger les stickers."
            );
        }

        stickerList.innerHTML = "";

        result.stickers.forEach(sticker => {
            const button = document.createElement("button");

            button.type = "button";
            button.classList.add("sticker-item");
            button.title = sticker.name;

            const img = document.createElement("img");

            img.src = sticker.url;
            img.alt = sticker.name;

            button.appendChild(img);

            button.addEventListener("click", () => {
                selectSticker(sticker, button);
            });

            stickerList.appendChild(button);
        });

        stickersLoaded = true;

    } catch (error) {
        console.error(error);
        console.error("Impossible de charger les stickers :", error.message);
    }
}

async function showStickerGallery() {
    stickerGallery.hidden = false;

    if (!stickersLoaded) {
        await loadStickers();
    }
}

document.addEventListener("camagru:photo-ready", () => {
    requestAnimationFrame(() => {
        resizeEditor();

        elementControls.hidden = false;
        elementSize.disabled = true;

        showStickerGallery();
    });
});

document.addEventListener("camagru:photo-reset", () => {
    selectedSticker = null;

    overlay.hidden = true;
    overlay.removeAttribute("src");

    elementControls.hidden = true;
    elementSize.disabled = true;

    stickerGallery.hidden = true;

    elementSize.value = 25;

    overlayX = 0;
    overlayY = 0;

    editor.style.width = "";
    editor.style.height = "";

    document.querySelectorAll(".sticker-item").forEach(item => {
        item.classList.remove("active");
    });
});

function selectSticker(sticker, button) {

    if (
        selectedSticker &&
        selectedSticker.filename === sticker.filename
    ) {
        selectedSticker = null;

        overlay.hidden = true;
        overlay.removeAttribute("src");

        elementSize.disabled = true;

        button.classList.remove("active");

        overlayX = 0;
        overlayY = 0;

        return;
    }

    selectedSticker = sticker;

    document.querySelectorAll(".sticker-item").forEach(item => {
        item.classList.remove("active");
    });

    button.classList.add("active");

    overlay.onload = () => {
        overlay.hidden = false;

        overlay.style.width = `${elementSize.value}%`;

        elementSize.disabled = false;

        requestAnimationFrame(() => {
            overlayX = (editor.clientWidth - overlay.offsetWidth) / 2;
            overlayY = (editor.clientHeight - overlay.offsetHeight) / 2;

            updatePosition();
        });
    };

    overlay.onerror = () => {
        console.error("Impossible de charger ce sticker.");

        selectedSticker = null;
        elementSize.disabled = true;

        button.classList.remove("active");
    };

    overlay.src = sticker.url;
}

function updatePosition() {
    overlay.style.left = `${overlayX}px`;
    overlay.style.top = `${overlayY}px`;
}

elementSize.addEventListener("input", () => {
    if (!selectedSticker || overlay.hidden) return;

    overlay.style.width = `${elementSize.value}%`;

    overlayX = Math.max(
        0,
        Math.min(overlayX, editor.clientWidth - overlay.offsetWidth)
    );

    overlayY = Math.max(
        0,
        Math.min(overlayY, editor.clientHeight - overlay.offsetHeight)
    );

    updatePosition();
});

overlay.addEventListener("pointerdown", event => {
    if (overlay.hidden) return;

    isDragging = true;

    overlay.setPointerCapture(event.pointerId);

    startX = event.clientX;
    startY = event.clientY;

    initialX = overlayX;
    initialY = overlayY;
});

overlay.addEventListener("pointermove", event => {
    if (!isDragging) return;

    const rect = editor.getBoundingClientRect();

    const scaleX = editor.clientWidth / rect.width;
    const scaleY = editor.clientHeight / rect.height;

    overlayX = initialX + (event.clientX - startX) * scaleX;
    overlayY = initialY + (event.clientY - startY) * scaleY;

    overlayX = Math.max(
        0,
        Math.min(overlayX, editor.clientWidth - overlay.offsetWidth)
    );

    overlayY = Math.max(
        0,
        Math.min(overlayY, editor.clientHeight - overlay.offsetHeight)
    );

    updatePosition();
});

overlay.addEventListener("pointerup", () => {
    isDragging = false;
});

overlay.addEventListener("pointercancel", () => {
    isDragging = false;
});

window.addEventListener("resize", () => {
    if (!photo.naturalWidth) return;

    const oldWidth = editor.clientWidth;
    const oldHeight = editor.clientHeight;

    const normalizedX = oldWidth ? overlayX / oldWidth : 0;
    const normalizedY = oldHeight ? overlayY / oldHeight : 0;

    resizeEditor();

    requestAnimationFrame(() => {
        overlayX = normalizedX * editor.clientWidth;
        overlayY = normalizedY * editor.clientHeight;

        if (!overlay.hidden) {
            overlayX = Math.max(
                0,
                Math.min(
                    overlayX,
                    editor.clientWidth - overlay.offsetWidth
                )
            );

            overlayY = Math.max(
                0,
                Math.min(
                    overlayY,
                    editor.clientHeight - overlay.offsetHeight
                )
            );

            updatePosition();
        }
    });
});

publishPhoto.addEventListener("click", async () => {

    if (!photo.src || !photo.naturalWidth) {
        console.error("Aucune photo principale.");
        return;
    }

    if (!selectedSticker || overlay.hidden) {
        console.error("Aucun sticker sélectionné.");
        return;
    }

    publishPhoto.disabled = true;
    publishPhoto.textContent = "Publication en cours...";

    try {
        const photoResponse = await fetch(photo.src);

        if (!photoResponse.ok) {
            throw new Error("Impossible de récupérer la photo.");
        }

        const photoBlob = await photoResponse.blob();

        const formData = new FormData();

        formData.append("csrf_token", csrfToken);

        formData.append("photo", photoBlob, "photo.jpg");
        formData.append("sticker", selectedSticker.filename);

        formData.append(
            "x",
            overlayX / editor.clientWidth
        );

        formData.append(
            "y",
            overlayY / editor.clientHeight
        );

        formData.append(
            "width",
            overlay.offsetWidth / editor.clientWidth
        );

        const response = await fetch("/camagru/publish", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || "Erreur lors de la publication."
            );
        }

        window.location.reload();

    } catch (error) {
        console.error("Erreur lors de la publication :", error);

        publishPhoto.disabled = false;
        publishPhoto.textContent = "Publier";
    }
});

elementControls.hidden = true;
elementSize.disabled = true;
