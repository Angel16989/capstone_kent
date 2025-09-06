</main>
<footer class="bg-dark text-light py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <p>&copy; <?php echo date('Y'); ?> L9 Fitness Gym. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-md-end">
        <a class="link-light me-3" href="<?php echo BASE_URL; ?>terms.php">Terms</a>
        <a class="link-light me-3" href="<?php echo BASE_URL; ?>privacy.php">Privacy</a>
        <a class="link-light me-3" href="<?php echo BASE_URL; ?>contact.php">Contact</a>
        <a class="link-light" href="<?php echo BASE_URL; ?>admin.php">Admin</a>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/page-transitions.js"></script>

<!-- Chatbot Scripts -->
<script>
  // Pass user info to chatbot if logged in
  <?php if (isset($_SESSION['user'])): ?>
  window.userInfo = {
    id: <?php echo $_SESSION['user']['id']; ?>,
    name: "<?php echo htmlspecialchars($_SESSION['user']['name']); ?>",
    email: "<?php echo htmlspecialchars($_SESSION['user']['email']); ?>"
  };
  <?php endif; ?>
</script>
<script src="<?php echo BASE_URL; ?>assets/js/chatbot.js?v=<?php echo time(); ?>"></script>

</body>
</html>
