<?php

namespace ryunosuke\dbml;

require_once __DIR__ . '/../vendor/autoload.php';

$directory = __DIR__ . "/../src";
file_put_contents("$directory/functions.php", \ryunosuke\Functions\Transporter::exportNamespace(__NAMESPACE__, false, "$directory/dbml"));
file_put_contents("$directory/functions-dev.php", \ryunosuke\Functions\Transporter::exportNamespace(__NAMESPACE__));
