<?php

require_once(dirname( __FILE__ ) . '/captcha.php');

session_start();
$captcha = new Mk_Artbees_Captcha();

$captcha->imageFormat = 'png';
$captcha->CreateImage();
