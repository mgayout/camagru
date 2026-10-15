<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Camagru') ?></title>
    <link rel="icon" type="images/png" href="/images/favicon.png" />
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php require $headerView; ?>
    <?php require $mainView; ?>
    <?php require FOOTER_VIEW; ?>
</body>
</html>