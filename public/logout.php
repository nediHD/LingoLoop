<?php
require_once __DIR__ . '/../core/SessionManager.php';

SessionManager::destroy();

header("Location: /lingoloop/public/index.php");
exit();
