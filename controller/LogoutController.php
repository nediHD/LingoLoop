<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';


SessionManager::destroy();

header("Location: /lingoloop/view/?action=login");

exit();
