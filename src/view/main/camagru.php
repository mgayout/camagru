<link rel="stylesheet" href="/assets/main.css">
<link rel="stylesheet" href="/assets/camagru.css">
<script type="module" src="/scripts/camagru/webcam.js" defer></script>
<script type="module" src="/scripts/camagru/editor.js" defer></script>
<main>
    <div class="sidebar">
        <button id="back" type="button" hidden>Retour</button>
        <button id="startCamera" type="button">Activer la webcam</button>
        <button id="importImage" type="button">Importer une image</button>
        <input type="file" id="photoInput" accept="image/png, image/jpeg" hidden/>
        <div id="stickerGallery" hidden>
            <div id="stickerList"></div>
        </div>
        <div id="elementControls" hidden>
            <label for="elementSize"></label>
            <input type="range" id="elementSize" min="5" max="50" value="25" disabled/>
        </div>
    </div>
    <div class="content">
        <div class="visual-container" id="cameraContainer">
            <video id="webcam" autoplay playsinline></video>
            <canvas id="canvas" hidden></canvas>
        </div>
        <button id="takePhoto" type="button"></button>
        <div id="photoView" hidden>
            <div class="editor" id="editor">
                <img id="photo" alt="Photo sélectionnée">
                <img id="overlay" alt="Élément de superposition" hidden draggable="false">
            </div>
        </div>
        <button id="publishPhoto" type="button" hidden>Publier</button>
    </div>
</main>
