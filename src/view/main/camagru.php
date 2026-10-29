<link rel="stylesheet" href="/assets/camagru.css">
<script type="module" src="/scripts/camagru/webcam.js" defer></script>
<main>
    <div class="webcam-container" id="webcamContainer">
        <video id="webcam" autoplay playsinline muted></video>
        <input type="file" id="photoInput" accept="image/*" hidden>
        <div class="webcam-actions" id="cameraActions">
            <button id="startCamera" type="button">Activer la webcam</button>
            <button id="takePhoto" type="button" disabled>Prendre une photo</button>
            <button id="importPhoto" type="button">Importer une photo</button>
        </div>
        <p id="cameraStatus" role="status" aria-live="polite" hidden></p>
        <canvas id="canvas" hidden></canvas>
        <div id="photoView" hidden>
            <img id="photo" alt="Aperçu de la photo sélectionnée">
            <button id="retakePhoto" type="button">Reprendre</button>
        </div>
    </div>
</main>
