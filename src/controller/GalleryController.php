<?php

class GalleryController
{
    private Database $database;
    private PDO $pdo;

    private const PHOTOS_PER_PAGE = 8;

    public function __construct()
    {
        $this->database = new Database();
        $this->pdo = $this->database->getConnection();
    }

    private function renderGallery(array $errors, ?int $user_id, int $currentPage): void
    {
        $username = $_SESSION['username'] ?? null;
        $email = $_SESSION['email'] ?? null;

        $offset = ($currentPage - 1) * self::PHOTOS_PER_PAGE;
        $photos = $this->database->getPhotosPaginated($user_id, self::PHOTOS_PER_PAGE, $offset);
        $totalPages = (int) ceil($this->database->getTotalPhotos() / self::PHOTOS_PER_PAGE);
        $commentsByPhoto = $this->database->getCommentsByPhotoIds(array_column($photos, 'id'));

        $title = 'Gallerie';
        $headerView = isset($user_id) ? HEADERON_VIEW : HEADEROFF_VIEW;
        $mainView = GALLERY_VIEW;

        require LAYOUT_VIEW;
    }

    public function index(): void
    {
        $errors = [];
        $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $currentPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);

        if ($currentPage === false || $currentPage < 1) {
            $currentPage = 1;
        }

        $this->renderGallery($errors, $user_id, $currentPage);
    }

    public function like(): void
    {
        $errors = [];

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Vous devez être connecté pour aimer une photo.'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'La méthode n\'est pas autorisée'
            ]);
            return;
        }

        if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Token CSRF invalide.'
            ]);
            return;
        }

        $photo_id = filter_var($_POST['photo_id'] ?? null, FILTER_VALIDATE_INT);

        if ($photo_id === false || $photo_id <= 0) {
            http_response_code(400);
            return;
        }

        try {
            $user_id = (int) $_SESSION['user_id'];
            $email = $this->database->getEmailByPhotoId($photo_id);
            $username = $_SESSION['username'] ?? null;
            $receiver = $this->database->getUsernameByPhotoId($photo_id);
            $currentPage = filter_var($_POST['page'] ?? 1, FILTER_VALIDATE_INT);
            $liked = $this->database->getLike($photo_id, $user_id);

            if ($liked) {
                $this->database->setLikeOff($photo_id, $user_id);
            } else {
                $this->database->setLikeOn($photo_id, $user_id);
                $this->sendNotificationMail($email, $receiver, $username, "like");
            }
            $this->renderGallery($errors, $user_id, $currentPage);
            exit;
        }
        catch(PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            return;
        }
    }

    public function comment(): void
    {
        $errors = [];

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Vous devez être connecté pour commenter une photo.'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'La méthode n\'est pas autorisée.'
            ]);
            return;
        }

        if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Token CSRF invalide.'
            ]);
            return;
        }

        $photo_id = filter_var($_POST['photo_id'] ?? null, FILTER_VALIDATE_INT);

        if ($photo_id === false || $photo_id <= 0) {
            http_response_code(400);
            return;
        }

        $data = trim($_POST['content'] ?? '');

        if ($data === '' || mb_strlen($data) > 1000) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Le commentaire doit contenir entre 1 et 1000 caractères.']);
                return;
        }

        try {
            $user_id = (int) $_SESSION['user_id'];
            $email = $this->database->getEmailByPhotoId($photo_id);
            $username = $_SESSION['username'] ?? null;
            $receiver = $this->database->getUsernameByPhotoId($photo_id);
            $currentPage = filter_var($_POST['page'] ?? 1, FILTER_VALIDATE_INT);
            $this->database->setComment($photo_id, $user_id, $data);
            $this->sendNotificationMail($email, $receiver, $username, "comment");
            $this->renderGallery($errors, $user_id, $currentPage);
            exit;
        }
        catch(PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            return;
        }
    }

    public function sendNotificationMail(string $email, string $receiver, string $sender, string $like_or_comment): bool
    {

        if ($receiver === $sender) {
            return false;
        }

        if (!$this->database->getNotificationStatus($receiver, $like_or_comment)) {
            return false;
        }

        $subject = "Notification";

        $messages = [
            "like" => "Bonjour {$receiver} !\n\n{$sender} a aimé votre photo !\n\nQuel succès !",
            "comment" => "Bonjour {$receiver} !\n\n{$sender} a commenté votre photo !\n\nDépêches-toi d'aller lire son commentaire !"
        ];
        
        $headers = [
            "From" => "noreply@camagru.test",
            "Content-Type" => "text/plain; charset=UTF-8",];

        $result = mail($email, $subject, $messages[$like_or_comment], $headers);

        return $result;
    }
}
