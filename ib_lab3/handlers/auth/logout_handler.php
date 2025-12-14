<?php
require_once __DIR__ . '/../../helpers/session_helper.php';

logout();
header('Location: /pages/auth/login.php');
exit();

