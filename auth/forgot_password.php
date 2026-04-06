<?php
require_once __DIR__ . '/../config/store.php';

set_flash('error', 'Password reset is disabled for this project.');
redirect_to('login.php');
