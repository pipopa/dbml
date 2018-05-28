<?php

namespace ryunosuke\dbml;

require_once __DIR__ . '/../vendor/autoload.php';

file_put_contents(__DIR__ . "/../src/functions.php", \ryunosuke\Functions\Transporter::exportNamespace(__NAMESPACE__));
