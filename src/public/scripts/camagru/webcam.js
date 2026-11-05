
const video = document.getElementById("webcam");
const canvas = document.getElementById("canvas");
const photo = document.getElementById("photo");
const photoInput = document.getElementById("photoInput");

const startButton = document.getElementById("startCamera");
const takeButton = document.getElementById("takePhoto");
const importButton = document.getElementById("importImage");
const addElements = document.getElementById("addElements");

const cameraContainer = document.getElementById("cameraContainer");
const photoView = document.getElementById("photoView");
const retakeButton = document.getElementById("back");

let stream = null;
let photoUrl = null;
let cameraStarting = false;

function displayPhoto(blob) {
    if (photoUrl) {
        URL.revokeObjectURL(photoUrl);
    }

    photoUrl = URL.createObjectURL(blob);
    photo.src = photoUrl;

    stopCamera();

    cameraContainer.hidden = true;
    takeButton.hidden = true;
    startButton.hidden = true;
    importButton.hidden = true;

    photoView.hidden = false;
    retakeButton.hidden = false;
    addElements.hidden = false;
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }

    video.srcObject = null;

    takeButton.disabled = true;
    startButton.disabled = false;
    startButton.textContent = "Activer la webcam";
    startButton.classList.remove("camera-on");
}

startButton.addEventListener("click", async () => {
    if (stream) {
        stopCamera();
        return;
    }

    if (cameraStarting) return;

    if (!navigator.mediaDevices?.getUserMedia) {
        alert("Webcam indisponible. Utilise HTTPS ou localhost.");
        return;
    }

    cameraStarting = true;
    startButton.disabled = true;

    try {
        const newStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "user",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        });

        stream = newStream;
        video.srcObject = stream;

        await video.play();

        takeButton.disabled = false;
        startButton.textContent = "Éteindre la webcam";
        startButton.classList.add("camera-on");

    } catch (error) {
        const messages = {
            NotAllowedError:
                "Accès refusé. Autorise la webcam dans ton navigateur.",
            NotFoundError:
                "Aucune webcam détectée.",
            NotReadableError:
                "La webcam est occupée ou inaccessible."
        };

        alert(
            messages[error.name] ||
            "Impossible d'activer la webcam : " + error.message
        );

    } finally {
        cameraStarting = false;
        startButton.disabled = false;
    }
});

takeButton.addEventListener("click", () => {
    if (!stream || video.videoWidth === 0) {
        alert("La webcam n'est pas prête.");
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const context = canvas.getContext("2d");

    context.drawImage(
        video,
        0,
        0,
        canvas.width,
        canvas.height
    );

    canvas.toBlob(blob => {
        if (!blob) {
            alert("Impossible de créer la photo.");
            return;
        }

        displayPhoto(blob);
    }, "image/jpeg", 0.92);
});

importButton.addEventListener("click", () => {
    photoInput.click();
});

photoInput.addEventListener("change", () => {
    const file = photoInput.files[0];

    if (!file) return;

    if (!file.type.startsWith("image/")) {
        alert("Veuillez sélectionner une image.");
        photoInput.value = "";
        return;
    }

    if (file.size > 15 * 1024 * 1024) {
        alert("L'image ne doit pas dépasser 15 Mo.");
        photoInput.value = "";
        return;
    }

    const image = new Image();
    const temporaryUrl = URL.createObjectURL(file);

    image.onload = () => {
        URL.revokeObjectURL(temporaryUrl);
        displayPhoto(file);
        photoInput.value = "";
    };

    image.onerror = () => {
        URL.revokeObjectURL(temporaryUrl);
        alert("Impossible de lire cette image.");
        photoInput.value = "";
    };

    image.src = temporaryUrl;
});

retakeButton.addEventListener("click", () => {

    cameraContainer.hidden = false;
    takeButton.hidden = false;
    startButton.hidden = false;
    importButton.hidden = false;

    photoView.hidden = true;
    retakeButton.hidden = true;
    addElements.hidden = true;

    photo.removeAttribute("src");

    if (photoUrl) {
        URL.revokeObjectURL(photoUrl);
        photoUrl = null;
    }

    photoInput.value = "";
});

// Libérer les ressources quand l'utilisateur quitte la page
window.addEventListener("pagehide", () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }

    if (photoUrl) {
        URL.revokeObjectURL(photoUrl);
        photoUrl = null;
    }
});
