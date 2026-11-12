<?php

class CamagruController
{
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

		// Réponse JSON
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
		$directory = dirname(__DIR__) . '/uploads';

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

		// Réponse
		$sendResponse(true, 'Superposition réussie !', [
			'filename' => $filename,
			'width' => $photoWidth,
			'height' => $photoHeight
		]);
	}

	public function getStickers(): void
	{
		$directory = dirname(__DIR__) . '/public/stickers';

		if (!is_dir($directory)) {
			echo json_encode([
				'success' => false,
				'message' => 'Dossier stickers introuvable']);
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
				'url' => '/stickers/' . rawurlencode($file)];
		}
		
		echo json_encode([
			'success' => true,
			'stickers' => $stickers]);
		
		exit;
	}

}
