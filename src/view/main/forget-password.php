<main>
    <h1>Mot de passe oublié</h1>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
        <form action="/forgot-password" method="POST">
    	    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div>
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>
            <button type="submit">
                Recevoir un lien de réinitialisation
            </button>
        </form>
</main>