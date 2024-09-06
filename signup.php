<div class="container">
    <h1>Inscription</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de inscrição -->
    <form action="index.php" method="post">
        <label for="courriel1">Adresse de courriel 1:</label>
        <input type="email" id="courriel1" name="courriel1" required>

        <label for="courriel2">Adresse de courriel 2:</label>
        <input type="email" id="courriel2" name="courriel2" required>

        <label for="password1">Mot de passe 1:</label>
        <input type="password" id="password1" name="password1" required>

        <label for="password2">Mot de passe 2:</label>
        <input type="password" id="password2" name="password2" required>

        <button type="submit">Soumettre</button>

        <?php if ($success): ?>
        <p class="success">Inscription réussie!</p>
        <!-- Botão para redirecionar para a página de login -->
        <a href="login.php">
            <button type="button">Aller à la connexion</button>
        </a>
    <?php endif; ?>
    </form>
</div>
