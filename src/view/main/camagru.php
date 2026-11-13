<link rel="stylesheet" href="/assets/camagru/main.css">
<link rel="stylesheet" href="/assets/camagru/left-sidebar.css">
<link rel="stylesheet" href="/assets/camagru/content.css">
<link rel="stylesheet" href="/assets/camagru/right-sidebar.css">
<script type="module" src="/scripts/camagru/webcam.js" defer></script>
<script type="module" src="/scripts/camagru/editor.js" defer></script>
<script type="module" src="/scripts/camagru/gallery.js" defer></script>
<main>
    <div class="left-sidebar">
        <button id="back" type="button" hidden>Retour</button>
        <button id="startCamera" type="button">Activer la webcam</button>
        <button id="importImage" type="button">Importer une image</button>
        <input type="file" id="photoInput" accept="image/png, image/jpeg" hidden/>
        <div id="stickerGallery" hidden>
            <div id="stickerList"></div>
        </div>
        <div id="elementControls" hidden>
            <label for="elementSize"></label>
            <input type="range" id="elementSize" min="10" max="40" value="25" disabled/>
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
    <aside class="right-sidebar">
        <div id="photoGallery"></div>
    </aside>
</main>
