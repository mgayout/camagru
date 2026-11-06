<?php

header('Content-Type: application/json; charset=utf-8');

// Éviter que les erreurs PHP polluent la réponse JSON
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Fonction de réponse JSON
function sendResponse($success, $message, $extra = []) {
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));

    exit;
}

// Vérifier GD
if (!extension_loaded('gd')) {
    sendResponse(false, "GD n'est pas disponible.");
}

// Vérifier la réception du fichier
if (!isset($_FILES['photo'])) {
    sendResponse(false, "Aucune photo reçue.");
}

$file = $_FILES['photo'];

// Vérifier les erreurs d'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, "Erreur lors de l'upload : " . $file['error']);
}

// Vérifier le fichier image
$imageInfo = getimagesize($file['tmp_name']);

if ($imageInfo === false) {
    sendResponse(false, "Image invalide.");
}

// Vérifier les formats autorisés
$allowedTypes = [
    IMAGETYPE_JPEG,
    IMAGETYPE_PNG
];

if (!in_array($imageInfo[2], $allowedTypes, true)) {
    sendResponse(false, "Format d'image non autorisé.");
}

// Charger l'image avec GD
$imageData = file_get_contents($file['tmp_name']);

if ($imageData === false) {
    sendResponse(false, "Impossible de lire l'image.");
}

$image = imagecreatefromstring($imageData);

if ($image === false) {
    sendResponse(false, "Impossible de charger l'image avec GD.");
}

// Dimensions de l'image
$width = imagesx($image);
$height = imagesy($image);

// Chemin absolu du dossier uploads
$directory = __DIR__ . '/../../uploads';

// Créer le dossier si nécessaire
if (!is_dir($directory)) {
    if (!mkdir($directory, 0755, true)) {
        imagedestroy($image);
        sendResponse(false, "Impossible de créer le dossier uploads.");
    }
}

// Vérifier les permissions
if (!is_writable($directory)) {
    imagedestroy($image);
    sendResponse(false, "Le dossier uploads n'est pas accessible en écriture.");
}

// Chemin de sortie
$output = $directory . '/test.png';

// Préserver la transparence éventuelle
imagealphablending($image, false);
imagesavealpha($image, true);

// Enregistrer l'image avec GD
if (!imagepng($image, $output)) {
    imagedestroy($image);
    sendResponse(false, "Impossible d'enregistrer l'image.");
}

// Libérer la mémoire
imagedestroy($image);

// Réponse
sendResponse(true, "Image traitée avec GD !", [
    "width" => $width,
    "height" => $height
]);

?>
