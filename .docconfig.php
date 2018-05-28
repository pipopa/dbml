<?php

$rdi = new \RecursiveDirectoryIterator(__DIR__ . '/src', \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME);
$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::LEAVES_ONLY);
$filelist = iterator_to_array($rii);

return [
    'cachekey'   => md5(implode(',', array_map('md5_file', $filelist))),
    'title'      => 'dbml Class Reference',
    'frontpage'  => 'ryunosuke\\dbml\\',
    'menusize'   => 35,
    'source-map' => [
        '.*/Doctrine/DBAL/' => 'https://github.com/doctrine/dbal/blob/v2.7.1/lib/Doctrine/DBAL/',
        '.*/'               => 'https://github.com/arima-ryunosuke/dbml/blob/master/src/dbml/',
    ],
];
