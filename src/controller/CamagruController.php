<?php

class CamagruController
{
    private Database $database;
    private PDO $pdo;

    private const STICKER_DIRECTORY = '/public/stickers';
    private const UPLOAD_DIRECTORY = '/public/uploads';

    public function __construct()
    {
        $this->database = new Database();
        $this->pdo = $this->database->getConnection();
    }

    private function renderCamagru(array $errors, ?int $user_id): void
    {
        $username = $_SESSION['username'] ?? null;
        $email = $_SESSION['email'] ?? null;

        $title = 'Page d\'accueil';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = CAMAGRU_VIEW;
    
        require LAYOUT_VIEW;
    }

    public function index(): void
    {
        $errors = [];
        $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        $this->renderCamagru($errors, $user_id);
    }

    public function publishContent(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            $this->sendJsonResponse(false, 'Token CSRF invalide.');
        }

        $output = null;

        try {
            $request = $this->validateRequest();
            $stickerPath = $this->getStickerPath($request['sticker']);
            $coordinates = $this->getImageCoordinates();
            $composition = $this->createComposition($request['photo'], $stickerPath, $coordinates);
            $output = $this->saveComposition($composition['image']);
            $this->database->setPhoto($request['user_id'], $output['filename']);
            $this->sendJsonResponse(true, 'Superposition réussie !', ['filename' => $output['filename'], 'width' => $output['width'], 'height' => $output['height']]);
        } catch (Throwable $e) {
            if ($output !== null && is_file($output['path'])) {
                unlink($output['path']);
            }
            error_log($e->getMessage());
            $this->sendJsonResponse(false, $e instanceof InvalidArgumentException ? $e->getMessage() : 'Une erreur est survenue lors de la publication');
        }
    }

    public function validateRequest(): array
    {
        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id || !filter_var($user_id, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Utilisateur non connecté.');
        }

        if (!extension_loaded('gd')) {
            throw new InvalidArgumentException('GD n\'est pas disponible.');
        }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Photo principale manquante ou invalide.');
        }

        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Fichier trop volumineux.');
        }

        $photoPath = $_FILES['photo']['tmp_name'];

        if (!is_uploaded_file($photoPath)) {
            throw new InvalidArgumentException('Fichier uploadé invalide.');
        }

        $sticker = $_POST['sticker'] ?? '';

        if (!is_string($sticker) || $sticker === '' || basename($sticker) !== $sticker || strtolower(pathinfo($sticker, PATHINFO_EXTENSION)) != 'png') {
            throw new InvalidArgumentException('Sticker Invalide.');
        }
        return [
            'user_id' => (int) $user_id,
            'photo' => $photoPath,
            'sticker' => $sticker];
    }

    private function getStickerPath(string $filename): string
    {
        $directory = dirname(__DIR__) . self::STICKER_DIRECTORY;

        if (!is_dir($directory)) {
            throw new InvalidArgumentException('Dossier stickers introuvable');
        }

        $path = $directory . '/' . $filename;

        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException('Sticker introuvable.');
        }

        $info = getimagesize($path);

        if (!$info || $info[2] !== IMAGETYPE_PNG) {
            throw new InvalidArgumentException('Le sticker doit être une image PNG valide.');
        }

        return $path;
    }

    private function getImageCoordinates(): array
    {
        $x = filter_var($_POST['x'] ?? null, FILTER_VALIDATE_FLOAT);
        $y = filter_var($_POST['y'] ?? null, FILTER_VALIDATE_FLOAT);
        $width = filter_var($_POST['width'] ?? null, FILTER_VALIDATE_FLOAT);

        if (
            $x === false || $x === null ||
            $y === false || $y === null ||
            $width === false || $width === null ||
            $x < 0 || $y < 0 ||
            $width <= 0 || $width > 1 ||
            $x > 1 || $y > 1
        ) {
            throw new InvalidArgumentException('Coordonées invalides.');
        }

        return ['x' => $x, 'y' => $y, 'width' => $width];
    }

    private function loadImages(string $photoPath, string $stickerPath): array
    {
        $photoInfo = getimagesize($photoPath);

        if (!$photoInfo || !in_array($photoInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new InvalidArgumentException('Photo principale invalide.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($photoPath);

        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new InvalidArgumentException('Format de photo non autorisé.');
        }

        $photoData = file_get_contents($photoPath);

        if ($photoData === false) {
            throw new RuntimeException('Impossible de lire la photo.');
        }

        $photo = imagecreatefromstring($photoData);

        if (!$photo) {
            throw new RuntimeException('Impossible de charger la photo.');
        }

        $element = imagecreatefrompng($stickerPath);

        if (!$element) {
            imagedestroy($photo);
            throw new RuntimeException('Impossible de charger le sticker.');
        }
        return [
            'photo' => $photo,
            'element' => $element];
    }

    private function createComposition(string $photoPath, string $stickerPath, array $coordinates): array
    {
        $images = $this->loadImages($photoPath, $stickerPath);

        $photo = $images['photo'];
        $element = $images['element'];

        try {
            $photoWidth = imagesx($photo);
            $photoHeight = imagesy($photo);
            
            $elementWidth = imagesx($element);
            $elementHeigh = imagesy($element);
            
            $destWidth = (int) round($photoWidth * $coordinates['width']);
            $destHeight = (int) round($destWidth * ($elementHeigh / $elementWidth));
            
            $destX = (int) round($photoWidth * $coordinates['x']);
            $destY = (int) round($photoHeight * $coordinates['y']);
            
            if ($destWidth <= 0 || $destHeight <= 0 || 
                $destX + $destWidth > $photoWidth || 
                $destY + $destHeight > $photoHeight) {
                throw new InvalidArgumentException('Le sticker dépasse les limites de la photo');
            }
            
            imagealphablending($element, false);
            imagesavealpha($element, true);

            imagealphablending($photo, true);

            $success = imagecopyresampled($photo, $element, $destX, $destY, 0, 0, $destWidth, $destHeight, $elementWidth, $elementHeigh);

            if (!$success) {
                throw new RuntimeException('Impossible de superposer le sticker');
            }
            return [
                'image' => $photo,
                'width' => $photoWidth,
                'height' => $photoHeight];
        } catch (Throwable $e) {
            imagedestroy($photo);
            throw $e;
        } finally {
            imagedestroy($element);
        }
    }

    private function saveComposition(GdImage $image): array
    {
        $directory = dirname(__DIR__) . self::UPLOAD_DIRECTORY;

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            imagedestroy($image);
            throw new RuntimeException('Dossier uploads non accessible en écriture');
        }

        $filename = bin2hex(random_bytes(16)) . '.png';
        $path = $directory . '/' . $filename;

        try {
            $saved = imagepng($image, $path);
            
            if (!$saved) {
                throw new RuntimeException('Impossible d\'enregistrer le montage.');
            }

            return [
                'filename' => $filename,
                'path' => $path,
                'width' => imagesx($image),
                'height' => imagesy($image)];
        }
        catch (Throwable $e) {
            if (is_file($path)) {
                unlink($path);
            }
            throw $e;
        }
        finally {
            imagedestroy($image);
        }
    }

    public function getStickers(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $stickers = $this->scanStickers();

            $this->sendJsonResponse(true, 'Stickers récupérés.', ['stickers' => $stickers]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $this->sendJsonResponse(false, 'Impossible de récupérer les stickers.');
        }
    }

    private function scanStickers(): array
    {
        $directory = dirname(__DIR__) . '/public/stickers';

        if (!is_dir($directory)) {
            throw new RuntimeException('Dossier stickers introuvable.');
        }

        $files = scandir($directory);

        if ($files === false) {
            throw new RuntimeException('Impossible de lire le dossier stickers.');
        }

        $stickers = [];

        foreach ($files as $file) {
            if ($file === '.'|| $file === '..' || strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'png') {
                continue;
            }
        
            $path = $directory . '/' . $file;

            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $stickers[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'filename' => $file,
                'url' => '/stickers/' . rawurlencode($file)];
        }
        return $stickers;
    }

    public function getPhotos(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            http_response_code(401);
            $this->sendJsonResponse(false, 'Utilisateur non connecté.');
        }

        try {
            $photos = $this->database->getPhotosByUserId((int) $user_id);

            $formattedPhotos = $this->formatPhotos($photos);

            $this->sendJsonResponse(true, 'Photos récupérées.', ['photos' => $formattedPhotos]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $this->sendJsonResponse(false, 'Impossible de récupérer les photos.');
        }
    }

    private function formatPhotos(array $photos): array
    {
        $formattedPhotos = [];

        foreach ($photos as $photo) {
            $formattedPhotos[] = [
                'id' => (int) $photo['id'],
                'filename' => $photo['filename'],
                'date' => $photo['created_at'],
                'url' => '/uploads/' . rawurlencode($photo['filename'])
            ];
        }
        return $formattedPhotos;
    }

    public function deletePhoto(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            $this->sendJsonResponse(false, 'Token CSRF invalide.');
        }

        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            http_response_code(401);
            $this->sendJsonResponse(false, 'Utilisateur non connecté.');
        }

        try {

            $photoId = $this->validatePhotoId();
            $photo = $this->database->getPhotoByIdAndUserId($photoId, (int) $user_id);

            if (!$photo) {
                http_response_code(404);
                $this->sendJsonResponse(false, 'Photo introuvable.');
            }

            $filename = $photo['filename'];

            if (!is_string($filename) || !preg_match('/\A[a-f0-9]{32}\.png\z/i', $filename)) {
                http_response_code(400);
                $this->sendJsonResponse(false, 'Nom de fichier invalide.');
            }
            
            $deleted = $this->database->deletePhotoByIdAndUserId($photoId, (int) $user_id);

            if (!$deleted) {
                http_response_code(404);
                $this->sendJsonResponse(false, 'La photo n\'a pas pu être supprimée.');
            }

            $this->deletePhotoFile($filename);

            $this->sendJsonResponse(true, 'Photo supprimée avec succès.');
        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            $this->sendJsonResponse(false, 'Erreur lors de la suppression de la photo.');
        }
    }

    private function validatePhotoId(): int
    {
        $photoId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if ($photoId === false || $photoId === null || $photoId <= 0) {
            throw new InvalidArgumentException('Identifiant de photo invalide.');
        }

        return $photoId;
    }

    private function deletePhotoFile(string $filename): void
    {
        $directory = dirname(__DIR__) . '/public/uploads';

        $path = $directory . '/' . $filename;

        if (is_file($path) && !unlink($path)) {
            error_log('Impossible de supprimer le fichier photo : ' . $filename);
        }
    }

        private function sendJsonResponse(bool $success, string $message, array $extra = []): void
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }
}