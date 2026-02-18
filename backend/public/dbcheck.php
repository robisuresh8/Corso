<?php
$cn = mysqli_connect('127.0.0.1','root','','corso',3306);
if (!$cn) { http_response_code(500); echo 'fail: '.mysqli_connect_error(); exit; }
$r = mysqli_query($cn,'SELECT 1');
echo 'ok';
