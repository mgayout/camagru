<link rel="stylesheet" href="/assets/gallery/main.css">
<main class="gallery-page">
    <div class="gallery-page">
        <?php if (empty($photos)): ?>
            <p class="empty-gallery">
                Aucune photo disponible pour le moment.
            </p>
        <?php else: ?>
            <section class="gallery-grid">
                <?php foreach ($photos as $photo): ?>
                    <article class="gallery-card">
                        <img src="/uploads/<?= htmlspecialchars(rawurlencode($photo['filename']), ENT_QUOTES, 'UTF-8') ?>" alt="Photo publiée" loading="lazy">
                        <p class="gallery-date"><?= htmlspecialchars(date('d/m/Y à H:i',strtotime($photo['created_at'])), ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
            <?php if ($totalPages > 1): ?>
                <nav class="gallery-pagination" aria-label="Pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="pagination-button">Précédent</a>
                    <?php endif; ?>
                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <a href="?page=<?= $page ?>" class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>>
                            <?= $page ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="pagination-button">Suivant</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
