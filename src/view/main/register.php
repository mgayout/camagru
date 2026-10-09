<h1>Créer un compte</h1>

<?php if (!empty($errors)): ?>
	<ul>
		<?php foreach ($errors as $error): ?>
			<li><?= htmlspecialchars($error) ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form action="/register" method="POST">
	<div>
		<label for="username">Nom d'utilisateur</label>
		<input
			type="text"
			id="username"
			name="username"
			value="<?= htmlspecialchars($username ?? '') ?>"
			required
		>
	</div>

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

	<button type="submit">Créer mon compte</button>
</form>