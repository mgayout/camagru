<link rel="stylesheet" href="/assets/settings.css">
<script type="module" src="scripts/settings/sidebar_animation.js" defer></script>
<script type="module" src="scripts/settings/events.js" defer></script>
<main class="account-settings">

    <div class="settings-header">
        <h1>Paramètres du compte</h1>
        <p>Gérez vos informations personnelles et vos préférences.</p>
    </div>

    <div class="settings-container">

        <!-- SIDEBAR -->
        <aside class="settings-sidebar">
            <nav>
                <a href="#profile" class="settings-link active" data-section="profile">
                    👤 Informations personnelles
                </a>

                <a href="#security" class="settings-link" data-section="security">
                    🔒 Sécurité
                </a>

                <a href="#notifications" class="settings-link" data-section="notifications">
                    🔔 Notifications
                </a>

                <a href="#delete-account" class="settings-link danger-link" data-section="delete-account">
                    🗑️ Supprimer mon compte
                </a>
            </nav>
        </aside>

        <!-- CONTENU DES PARAMÈTRES -->
        <div class="settings-content">

            <!-- PROFIL -->
            <section class="settings-card settings-panel active" id="profile">
                <h2>Informations personnelles</h2>
                <p class="section-description">
                    Modifiez les informations associées à votre compte.
                </p>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input 
                        type="text"
                        id="username"
                        name="username"
                        data-setting="username"
                        value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="50"
                        required>
                </div>
                <button type="submit" class="btn-primary username-submit">
                    Enregistrer la modification
                </button>
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        data-setting="email"
                        value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="50"
                        required>
                </div>
                <button type="submit" class="btn-primary">
                    Enregistrer la modification
                </button>
            </section>

            <!-- SÉCURITÉ -->
            <section class="settings-card settings-panel" id="security" hidden>
                <h2>Sécurité</h2>
                <p class="section-description">
                    Gérez votre mot de passe et la sécurité de votre compte.
                </p>
                <div data-setting="password">
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            autocomplete="new-password"
                            minlength="12"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            autocomplete="new-password"
                            minlength="12"
                            required>
                    </div>
                    <button type="submit" class="btn-primary">
                        Modifier le mot de passe
                    </button>
                </div>
            </section>

            <!-- NOTIFICATIONS -->
            <section class="settings-card settings-panel" id="notifications" hidden>
                <h2>Notifications par e-mail</h2>
                <p class="section-description">
                    Choisissez les notifications que vous souhaitez recevoir.
                </p>
                <label class="toggle-option">
                    <span>
                        <strong>Commentaire</strong>
                        <small>Recevoir une notification à chaque commentaire sur vos photos.</small>
                    </span>
                    <input
                        type="checkbox"
                        id="comment_notification"
                        data-notification="comment"
                        <?= !empty($commentNotification) ? 'checked' : '' ?>>
                </label>
                <label class="toggle-option">
                    <span>
                        <strong>Like</strong>
                        <small>Recevoir une notification à chaque like sur vos photos.</small>
                    </span>
                    <input
                        type="checkbox"
                        id="like_notification"
                        data-notification="like"
                        <?= !empty($likeNotification) ? 'checked' : '' ?>>
                </label>
            </section>

            <!-- SUPPRESSION DU COMPTE -->
            <section class="settings-card settings-panel danger-card"
                id="delete-account" hidden>
                <h2>Supprimer mon compte</h2>
                <p>
                    Cette action peut entraîner la perte définitive de tes données.
                </p>

                <form action="/delete" method="POST">
                    <button type="submit" class="btn-danger">
                        Supprimer mon compte
                    </button>
                </form>
            </section>

        </div>
    </div>
</main>