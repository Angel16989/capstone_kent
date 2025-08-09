<?php
function require_login(): void { if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; } }
function login_user(array $u): void { $_SESSION['user']=['id'=>$u['id'],'name'=>$u['first_name'].' '.$u['last_name'],'email'=>$u['email'],'role_id'=>(int)($u['role_id']??4)]; }
function logout_user(): void { session_destroy(); header('Location: index.php'); exit; }
function current_user(){ return $_SESSION['user'] ?? null; }
function is_admin(): bool { return (($_SESSION['user']['role_id'] ?? 4) === 1); }
