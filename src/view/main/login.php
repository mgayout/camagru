<main>
	<h1>Connexion</h1>
	<?php if (!empty($errors)): ?>
		<ul>
			<?php foreach ($errors as $error): ?>
				<li><?= htmlspecialchars($error) ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<form action="/login" method="POST">
	    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
		<div>
			<label for="email">Email</label>
			<input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
		</div>
		<div>
			<label for="password">Mot de passe</label>
			<input type="password" id="password" name="password" required>
			<a href="/forgot-password">Mot de passe oublié</a>
		</div>
		<button type="submit">Se connecter</button>
	</form>
</main>