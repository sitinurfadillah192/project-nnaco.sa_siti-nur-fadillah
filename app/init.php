<?php
require_once 'helpers/env.php';
require_once 'config/config.php';
require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/Model.php';
require_once 'core/Security.php';
require_once 'core/SecretVault.php';
require_once 'core/AiService.php';
require_once 'core/Flasher.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'middleware/RoleMiddleware.php';
require_once 'core/FonnteService.php';
require_once 'core/OtpService.php';
require_once 'repositories/OrderNotificationRepository.php';
require_once 'services/OrderNotificationService.php';

// Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
