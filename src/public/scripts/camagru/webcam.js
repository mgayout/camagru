
const video = document.getElementById("webcam");
const canvas = document.getElementById("canvas");
const photo = document.getElementById("photo");
const photoInput = document.getElementById("photoInput");

const startButton = document.getElementById("startCamera");
const takeButton = document.getElementById("takePhoto");
const importButton = document.getElementById("importPhoto");
const status = document.getElementById("cameraStatus");

const cameraActions = document.getElementById("cameraActions");
const photoView = document.getElementById("photoView");
const retakeButton = document.getElementById("retakePhoto");

let stream = null;
let photoUrl = null;
let cameraStarting = false;

// Affiche une photo capturée ou importée
function displayPhoto(blob) {
    if (photoUrl) {
        URL.revokeObjectURL(photoUrl);
    }

    photoUrl = URL.createObjectURL(blob);
    photo.src = photoUrl;

    // Arrêter la webcam
    stopCamera();

    // Masquer l'interface de capture
    video.hidden = true;
    cameraActions.hidden = true;
    status.hidden = true;

    // Afficher la photo en grand
    photoView.hidden = false;
}

// Arrête la webcam et réinitialise son bouton
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

// Active ou désactive la webcam
startButton.addEventListener("click", async () => {
    if (stream) {
        stopCamera();
        status.textContent = "Webcam désactivée.";
        return;
    }

    if (cameraStarting) return;

    if (!navigator.mediaDevices?.getUserMedia) {
        status.hidden = false;
        status.textContent =
            "Webcam indisponible. Utilise HTTPS ou localhost.";
        return;
    }

    cameraStarting = true;
    startButton.disabled = true;
    status.hidden = false;
    status.textContent = "Activation de la webcam...";

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

        status.textContent = "Webcam activée.";
    } catch (error) {
        const messages = {
            NotAllowedError:
                "Accès refusé. Autorise la webcam dans ton navigateur.",
            NotFoundError:
                "Aucune webcam détectée.",
            NotReadableError:
                "La webcam est occupée ou inaccessible."
        };

        status.textContent =
            messages[error.name] ||
            "Impossible d'activer la webcam : " + error.message;
    } finally {
        cameraStarting = false;
        startButton.disabled = false;
    }
});

// Capture une photo depuis la webcam
takeButton.addEventListener("click", () => {
    if (!stream || video.videoWidth === 0) {
        status.hidden = false;
        status.textContent = "La webcam n'est pas prête.";
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
            status.hidden = false;
            status.textContent = "Impossible de créer la photo.";
            return;
        }

        displayPhoto(blob);
    }, "image/jpeg", 0.92);
});

// Ouvre l'explorateur de fichiers
importButton.addEventListener("click", () => {
    photoInput.click();
});

// Importe et affiche une image
photoInput.addEventListener("change", () => {
    const file = photoInput.files[0];

    if (!file) return;

    if (!file.type.startsWith("image/")) {
        status.hidden = false;
        status.textContent = "Veuillez sélectionner une image.";
        photoInput.value = "";
        return;
    }

    // Limite la taille du fichier à 15 Mo
    if (file.size > 15 * 1024 * 1024) {
        status.hidden = false;
        status.textContent =
            "L'image ne doit pas dépasser 15 Mo.";
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
        status.hidden = false;
        status.textContent = "Impossible de lire cette image.";
        photoInput.value = "";
    };

    image.src = temporaryUrl;
});

// Revenir à l'interface webcam
retakeButton.addEventListener("click", () => {
    photoView.hidden = true;

    video.hidden = false;
    cameraActions.hidden = false;
    status.hidden = false;

    photo.removeAttribute("src");

    if (photoUrl) {
        URL.revokeObjectURL(photoUrl);
        photoUrl = null;
    }

    photoInput.value = "";
    status.textContent = "Tu peux reprendre une photo ou en importer une.";
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
