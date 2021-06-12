<?php
require dirname(__DIR__) . "/vendor/autoload.php";

use swostar\server\http\HttpServer;

(new HttpServer)->createServer();