<script type="module" src="/scripts/gallery/gallery.js" defer></script>
<link rel="stylesheet" href="/assets/gallery/main.css">
<link rel="stylesheet" href="/assets/gallery/photos.css">
<link rel="stylesheet" href="/assets/gallery/like.css">
<link rel="stylesheet" href="/assets/gallery/comment.css">
<link rel="stylesheet" href="/assets/gallery/pagination.css">
<main>
    <div class="gallery-container">
        <?php if (isset($_SESSION['comment_error'])): ?>
            <p class="comment-error">
                <?= htmlspecialchars($_SESSION['comment_error'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php unset($_SESSION['comment_error']); ?>
        <?php endif; ?>
        <?php if (empty($photos)): ?>
            <p class="empty-gallery">
                Aucune photo disponible pour le moment.
            </p>
        <?php else: ?>
            <section class="gallery-grid">
                <?php foreach ($photos as $photo): ?>
                    <article class="gallery-card" data-photo-id="<?= (int) $photo['id'] ?>">
                        <div class="photo-image-container">
                            <img class="gallery-photo" src="/uploads/<?= htmlspecialchars(rawurlencode($photo['filename']), ENT_QUOTES, 'UTF-8') ?>" alt="Photo publiée" loading="lazy">
                            <div class="photo-actions">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="/gallery/like" method="POST" class="like-form">
                                        <input type="hidden" name="photo_id" value="<?= (int) $photo['id'] ?>">
                                        <input type="hidden" name="page" value="<?= (int) $currentPage ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="like-button <?= !empty($photo['user_liked']) ? 'liked' : '' ?>" aria-label="Aimer cette photo" aria-pressed="<?= !empty($photo['user_liked']) ? 'true' : 'false' ?>">
                                            <img src="/images/like.png" alt="" >
                                            <span class="likes-count">
                                                <?= (int) $photo['likes_count'] ?>
                                            </span>
                                        </button>
                                    </form>
                                    <button type="button" class="comment-toggle" data-photo-id="<?= (int) $photo['id'] ?>" aria-label="Afficher les commentaires" aria-expanded="false">
                                        <img src="/images/comment.png" alt="">
                                        <span class="comments-count">
                                            <?= (int) $photo['comments_count'] ?>
                                        </span>
                                    </button>
                                <?php else: ?>
                                    <div class="photo-counter">
                                        <img src="/images/like.png" alt="Likes">
                                        <span>
                                            <?= (int) $photo['likes_count'] ?>
                                        </span>
                                    </div>
                                    <div class="photo-counter">
                                        <img src="/images/comment.png" alt="Commentaires">
                                        <span>
                                            <?= (int) $photo['comments_count'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="gallery-date">
                            <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($photo['created_at'])), ENT_QUOTES, 'UTF-8' ) ?>
                        </p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="comments-section" hidden>
                                <div class="comments-list">
                                    <?php $photoComments = $commentsByPhoto[$photo['id']] ?? [];?>
                                    <?php if (empty($photoComments)): ?>
                                        <p>Aucun commentaire pour le moment.</p>
                                    <?php else: ?>
                                        <?php foreach ($photoComments as $comment): ?>
                                            <div class="comment-item">
                                                <strong>
                                                    <?= htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') ?> :
                                                </strong>
                                                <span>
                                                    <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                                                </span>
                                                <small>
                                                    <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <form action="/gallery/comment" method="POST" class="comment-form">
                                    <input type="hidden" name="photo_id" value="<?= (int) $photo['id'] ?>">
                                    <input type="hidden" name="page" value="<?= (int) $currentPage ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="text" name="content" placeholder="Écrire un commentaire..." maxlength="1000" required>
                                    <button type="submit">
                                        Envoyer
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
            <?php if ($totalPages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="pagination-button">
                            Précédent
                        </a>
                    <?php endif; ?>
                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <a href="?page=<?= $page ?>" class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>>
                            <?= $page ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="pagination-button">
                            Suivant
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
