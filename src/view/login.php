<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Camagru</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="/login" method="POST">
        <div>
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email ?? '') ?>"
                required
            >
        </div>

        <div>
            <label for="password">Mot de passe</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>