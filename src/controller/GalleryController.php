<?php

class GalleryController
{
    private PDO $pdo;

    private const PHOTOS_PER_PAGE = 8;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function index(): void
    {
        $username = $_SESSION['username'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;

        $title = 'Galerie';

        $headerView = isset($user_id)
            ? HEADERON_VIEW
            : HEADEROFF_VIEW;

        $mainView = GALLERY_VIEW;

        // Récupérer le numéro de page
        $currentPage = filter_input(
            INPUT_GET,
            'page',
            FILTER_VALIDATE_INT
        );

        if (!$currentPage || $currentPage < 1) {
            $currentPage = 1;
        }

        try {
            // Compter le nombre total de photos
            $stmt = $this->pdo->query(
                'SELECT COUNT(*) FROM photos'
            );

            $totalPhotos = (int) $stmt->fetchColumn();

            // Calculer le nombre total de pages
            $totalPages = (int) ceil(
                $totalPhotos / self::PHOTOS_PER_PAGE
            );

            // Éviter de demander une page inexistante
            if ($totalPages > 0 && $currentPage > $totalPages) {
                $currentPage = $totalPages;
            }

            // Calculer le décalage SQL
            $offset = ($currentPage - 1) * self::PHOTOS_PER_PAGE;

            // Récupérer les photos de la page actuelle
            $stmt = $this->pdo->prepare(
                'SELECT id, user_id, filename, created_at
                 FROM photos
                 ORDER BY created_at DESC, id DESC
                 LIMIT :limit OFFSET :offset'
            );

            $stmt->bindValue(
                ':limit',
                self::PHOTOS_PER_PAGE,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':offset',
                $offset,
                PDO::PARAM_INT
            );

            $stmt->execute();

            $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log($e->getMessage());

            $photos = [];
            $totalPhotos = 0;
            $totalPages = 0;
            $currentPage = 1;
        }

        require LAYOUT_VIEW;
    }
}
