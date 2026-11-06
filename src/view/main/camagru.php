<link rel="stylesheet" href="/assets/main.css">
<link rel="stylesheet" href="/assets/camagru.css">
<script type="module" src="/scripts/camagru/webcam.js" defer></script>
<main>
    <div class="sidebar">
        <button id="back" type="button" hidden>Retour</button>
        <button id="addElements" type="button" hidden>Ajouter des éléments</button>
        <button id="startCamera" type="button">Activer la webcam</button>
        <button id="importImage" type="button">Importer une image</button>
        <input type="file" id="photoInput" accept="image/*" hidden/>
    </div>
    <div class="content">
        <div class="visual-container" id="cameraContainer">
            <video id="webcam" autoplay playsinline></video>
            <canvas id="canvas" hidden></canvas>
        </div>
        <button id="takePhoto" type="button"></button>
        <div id="photoView" hidden>
            <div class="visual-container">
                <img id="photo" alt="Photo sélectionnée">
            </div>
        </div>
        <button id="publishPhoto" type="button" hidden>Publier</button>
    </div>
</main>
