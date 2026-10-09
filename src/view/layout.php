<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Camagru') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

    <?php require __DIR__ . $headerView; ?>

    <main>
        <?php require $mainView; ?>
    </main>

    <?php require __DIR__ . $footerView; ?>

</body>
</html>