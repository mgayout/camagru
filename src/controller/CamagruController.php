<?php

class CamagruController
{
    private PDO $pdo;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function index(): void
    {
        $username = $_SESSION['username'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;

        $title = 'Page d\'accueil';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = CAMAGRU_VIEW;

        require LAYOUT_VIEW;
    }

    public function publishContent(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        $sendResponse = function (
            bool $success,
            string $message,
            array $extra = []
        ): void {
            echo json_encode(array_merge([
                'success' => $success,
                'message' => $message
            ], $extra));

            exit;
        };

        // Vérifier la connexion utilisateur
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $sendResponse(false, 'Utilisateur non connecté.');
        }

        // Vérifier GD
        if (!extension_loaded('gd')) {
            $sendResponse(false, 'GD n\'est pas disponible.');
        }

        // Vérifier la photo principale
        if (
            !isset($_FILES['photo']) ||
            $_FILES['photo']['error'] !== UPLOAD_ERR_OK
        ) {
            $sendResponse(false, 'Photo principale manquante ou invalide.');
        }

        // Récupérer le nom du sticker
        $stickerFilename = $_POST['sticker'] ?? '';

        if (
            !is_string($stickerFilename) ||
            $stickerFilename === '' ||
            basename($stickerFilename) !== $stickerFilename ||
            strtolower(pathinfo($stickerFilename, PATHINFO_EXTENSION)) !== 'png'
        ) {
            $sendResponse(false, 'Sticker invalide.');
        }

        // Vérifier le dossier des stickers
        $stickerDirectory = dirname(__DIR__) . '/public/stickers';

        if (!is_dir($stickerDirectory)) {
            $sendResponse(false, 'Dossier stickers introuvable.');
        }

        // Construire le chemin du sticker
        $stickerPath = $stickerDirectory . '/' . $stickerFilename;

        if (!is_file($stickerPath) || !is_readable($stickerPath)) {
            $sendResponse(false, 'Sticker introuvable.');
        }

        // Vérifier les coordonnées
        $x = filter_input(INPUT_POST, 'x', FILTER_VALIDATE_FLOAT);
        $y = filter_input(INPUT_POST, 'y', FILTER_VALIDATE_FLOAT);
        $width = filter_input(INPUT_POST, 'width', FILTER_VALIDATE_FLOAT);

        if (
            $x === false || $x === null ||
            $y === false || $y === null ||
            $width === false || $width === null ||
            $x < 0 || $y < 0 ||
            $width <= 0 || $width > 1 ||
            $x > 1 || $y > 1
        ) {
            $sendResponse(false, 'Coordonnées invalides.');
        }

        // Vérifier la photo principale
        $photoInfo = getimagesize($_FILES['photo']['tmp_name']);

        if (
            !$photoInfo ||
            !in_array($photoInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)
        ) {
            $sendResponse(false, 'Photo principale invalide.');
        }

        // Vérifier le sticker
        $stickerInfo = getimagesize($stickerPath);

        if (!$stickerInfo || $stickerInfo[2] !== IMAGETYPE_PNG) {
            $sendResponse(false, 'Le sticker doit être une image PNG valide.');
        }

        // Charger la photo principale
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);

        if ($photoData === false) {
            $sendResponse(false, 'Impossible de lire la photo.');
        }

        $photo = imagecreatefromstring($photoData);

        if (!$photo) {
            $sendResponse(false, 'Impossible de charger la photo.');
        }

        // Charger le sticker
        $element = imagecreatefrompng($stickerPath);

        if (!$element) {
            imagedestroy($photo);
            $sendResponse(false, 'Impossible de charger le sticker.');
        }

        // Dimensions originales
        $photoWidth = imagesx($photo);
        $photoHeight = imagesy($photo);

        $elementWidth = imagesx($element);
        $elementHeight = imagesy($element);

        // Calculer les dimensions réelles du sticker
        $destWidth = (int) round($photoWidth * $width);

        $destHeight = (int) round(
            $destWidth * ($elementHeight / $elementWidth)
        );

        // Calculer la position
        $destX = (int) round($photoWidth * $x);
        $destY = (int) round($photoHeight * $y);

        // Vérifier les limites de la photo
        if (
            $destWidth <= 0 ||
            $destHeight <= 0 ||
            $destX + $destWidth > $photoWidth ||
            $destY + $destHeight > $photoHeight
        ) {
            imagedestroy($photo);
            imagedestroy($element);

            $sendResponse(false, 'Le sticker dépasse les limites de la photo.');
        }

        // Préserver la transparence du sticker
        imagealphablending($element, false);
        imagesavealpha($element, true);

        // Activer le mélange alpha sur la photo
        imagealphablending($photo, true);

        // Superposition et redimensionnement
        imagecopyresampled(
            $photo,
            $element,
            $destX,
            $destY,
            0,
            0,
            $destWidth,
            $destHeight,
            $elementWidth,
            $elementHeight
        );

        // Dossier de destination
        $directory = dirname(__DIR__) . '/public/uploads';

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0755, true)
        ) {
            imagedestroy($photo);
            imagedestroy($element);

            $sendResponse(false, 'Impossible de créer le dossier uploads.');
        }

        if (!is_writable($directory)) {
            imagedestroy($photo);
            imagedestroy($element);

            $sendResponse(false, 'Dossier uploads non accessible en écriture.');
        }

        // Nom unique
        $filename = bin2hex(random_bytes(16)) . '.png';

        $output = $directory . '/' . $filename;

        // Enregistrer le montage
        $saved = imagepng($photo, $output);

        // Libérer la mémoire
        imagedestroy($photo);
        imagedestroy($element);

        if (!$saved) {
            $sendResponse(false, 'Impossible d\'enregistrer le montage.');
        }

        // Enregistrer la photo en base de données
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO photos (user_id, filename)
                 VALUES (:user_id, :filename)'
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':filename' => $filename
            ]);

        } catch (PDOException $e) {
            if (is_file($output)) {
                unlink($output);
            }

            error_log($e->getMessage());

            $sendResponse(
                false,
                'Erreur lors de l\'enregistrement en base de données.'
            );
        }

        // Réponse
        $sendResponse(true, 'Superposition réussie !', [
            'filename' => $filename,
            'width' => $photoWidth,
            'height' => $photoHeight
        ]);

    }

    public function getStickers(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $directory = dirname(__DIR__) . '/public/stickers';

        if (!is_dir($directory)) {
            echo json_encode([
                'success' => false,
                'message' => 'Dossier stickers introuvable.'
            ]);

            exit;
        }

        $stickers = [];

        foreach (scandir($directory) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'png') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (!is_file($path)) {
                continue;
            }

            $stickers[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'filename' => $file,
                'url' => '/stickers/' . rawurlencode($file)
            ];
        }

        echo json_encode([
            'success' => true,
            'stickers' => $stickers
        ]);

        exit;
    }

    public function getPhotos(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => 'Utilisateur non connecté.'
            ]);

            exit;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, filename, created_at
                 FROM photos
                 WHERE user_id = :user_id
                 ORDER BY created_at DESC, id DESC'
            );

            $stmt->execute([
                ':user_id' => $userId
            ]);

            $photos = [];

            while ($photo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $photos[] = [
                    'id' => $photo['id'],
                    'filename' => $photo['filename'],
                    'date' => $photo['created_at'],
                    'url' => '/uploads/' . rawurlencode($photo['filename'])
                ];
            }

            echo json_encode([
                'success' => true,
                'photos' => $photos
            ]);

        } catch (PDOException $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Impossible de récupérer les photos.'
            ]);
        }

        exit;
    }

    public function deletePhoto(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sendResponse = function (
            bool $success,
            string $message
        ): void {
            echo json_encode([
                'success' => $success,
                'message' => $message
            ]);

            exit;
        };

        // Vérifier la connexion utilisateur
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            $sendResponse(false, 'Utilisateur non connecté.');
        }

        // Vérifier l'identifiant de la photo
        $photoId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$photoId || $photoId <= 0) {
            http_response_code(400);
            $sendResponse(false, 'Identifiant de photo invalide.');
        }

        try {
            // Récupérer uniquement une photo appartenant à cet utilisateur
            $stmt = $this->pdo->prepare(
                'SELECT id, filename
                 FROM photos
                 WHERE id = :id AND user_id = :user_id'
            );

            $stmt->execute([
                ':id' => $photoId,
                ':user_id' => $userId
            ]);

            $photo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$photo) {
                http_response_code(404);
                $sendResponse(false, 'Photo introuvable.');
            }

            $filename = $photo['filename'];

            // Vérifier le nom du fichier
            if (
                !is_string($filename) ||
                !preg_match('/\A[a-f0-9]{32}\.png\z/i', $filename)
            ) {
                http_response_code(400);
                $sendResponse(false, 'Nom de fichier invalide.');
            }

            // Construire le chemin du fichier
            $directory = dirname(__DIR__) . '/public/uploads';
            $filePath = $directory . '/' . $filename;

            // Supprimer l'entrée de la base de données
            $stmt = $this->pdo->prepare(
                'DELETE FROM photos
                 WHERE id = :id AND user_id = :user_id'
            );

            $stmt->execute([
                ':id' => $photoId,
                ':user_id' => $userId
            ]);

            if ($stmt->rowCount() !== 1) {
                http_response_code(404);
                $sendResponse(false, 'La photo n\'a pas pu être supprimée.');
            }

            // Supprimer le fichier physique
            if (is_file($filePath) && !unlink($filePath)) {
                error_log(
                    'Impossible de supprimer le fichier photo : ' . $filename
                );
            }

            $sendResponse(true, 'Photo supprimée avec succès.');

        } catch (PDOException $e) {
            error_log($e->getMessage());

            http_response_code(500);

            $sendResponse(
                false,
                'Erreur lors de la suppression de la photo.'
            );
        }
    }
}
