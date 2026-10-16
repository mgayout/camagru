<link rel="stylesheet" href="/assets/settings.css">
<script src="scripts/settings.js" defer></script>
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

                <a href="#preferences" class="settings-link" data-section="preferences">
                    ⚙️ Préférences
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

                <form action="update_account.php" method="POST">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="firstname">Prénom</label>
                        <input type="text" id="firstname" name="firstname"
                            value="<?= htmlspecialchars($user['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="lastname">Nom</label>
                        <input type="text" id="lastname" name="lastname"
                            value="<?= htmlspecialchars($user['lastname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <button type="submit" class="btn-primary">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>

            <!-- SÉCURITÉ -->
            <section class="settings-card settings-panel" id="security" hidden>
                <h2>Sécurité</h2>
                <p class="section-description">
                    Gérez votre mot de passe et la sécurité de votre compte.
                </p>

                <form action="update_password.php" method="POST">
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password"
                            name="current_password"
                            autocomplete="current-password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password"
                            name="new_password"
                            autocomplete="new-password" minlength="12" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password"
                            name="confirm_password"
                            autocomplete="new-password" minlength="12" required>
                    </div>

                    <button type="submit" class="btn-primary">
                        Modifier le mot de passe
                    </button>
                </form>
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

            <!-- PRÉFÉRENCES -->
            <section class="settings-card settings-panel" id="preferences" hidden>
                <h2>Préférences</h2>
                <p class="section-description">
                    Personnalisez votre expérience.
                </p>

                <form action="update_preferences.php" method="POST">
                    <div class="form-group">
                        <label for="language">Langue</label>
                        <select id="language" name="language">
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="theme">Thème</label>
                        <select id="theme" name="theme">
                            <option value="light">Clair</option>
                            <option value="dark">Sombre</option>
                            <option value="system">Automatique</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">
                        Enregistrer les préférences
                    </button>
                </form>
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