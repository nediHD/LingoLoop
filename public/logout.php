<?php
require_once __DIR__ . '/../core/SessionManager.php';

SessionManager::destroy();

header("Location: /app/views/auth/login.php");
exit();
