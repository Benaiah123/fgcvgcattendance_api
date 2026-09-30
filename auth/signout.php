<?php
// public_html/api/auth/signout.php
require __DIR__ . '/../lib/cors.php'; 
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../lib/auth.php';

session_logout();

json_response(['success' => true]);