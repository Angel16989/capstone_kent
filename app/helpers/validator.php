<?php
function not_empty($v): bool { return isset($v) && trim((string)$v) !== ''; }
function email_valid($v): bool { return filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function sanitize($v): string { return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8'); }
