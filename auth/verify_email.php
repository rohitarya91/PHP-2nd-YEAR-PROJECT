<?php
require_once __DIR__ . '/../config/store.php';

set_flash('success', 'Email verification is not required. You can sign in directly.');
redirect_to('login.php');
