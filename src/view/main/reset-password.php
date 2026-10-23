<main>
    <h1>Réinitialiser mon mot de passe</h1>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p>
            Votre mot de passe a été modifié avec succès.
        </p>

        <a href="/login">Retour à la connexion</a>
    <?php else: ?>
        <form action="/reset-password" method="POST">
            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <div>
                <label for="password">Nouveau mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
            </div>

            <div>
                <label for="password_confirmation">
                    Confirmer le nouveau mot de passe
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit">
                Réinitialiser mon mot de passe
            </button>
        </form>

        <p>
            <a href="/login">Retour à la connexion</a>
        </p>
    <?php endif; ?>
</main>