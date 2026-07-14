    </main>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Template Esame. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script src="../assets/js/api.js"></script>
    <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
    <script>
        // Logout handler globale
        document.getElementById('btn-logout')?.addEventListener('click', async () => {
            try {
                await api.post('../api/auth/logout.php');
                window.location.href = 'login.php';
            } catch (e) {
                window.location.href = 'login.php';
            }
        });

        // Mobile menu toggle
        document.getElementById('navbar-toggle')?.addEventListener('click', () => {
            document.querySelector('.navbar-menu')?.classList.toggle('open');
        });
    </script>
    <?php endif; ?>
</body>
</html>
