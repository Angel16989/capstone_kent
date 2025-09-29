<?php
/**
 * Google OAuth Callback Handler (Root Copy)
 * This is a backup copy in the public root directory
 */

// Redirect to the actual callback file
header('Location: auth/google_callback.php?' . $_SERVER['QUERY_STRING']);
exit;
?>