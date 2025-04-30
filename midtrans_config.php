<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

Config::$serverKey = 'YOUR_MIDTRANS_SERVER_KEY';
Config::$clientKey = 'YOUR_MIDTRANS_CLIENT_KEY';
Config::$isProduction = false; // Ubah ke true untuk production
Config::$isSanitized = true;
Config::$is3ds = true;
?>