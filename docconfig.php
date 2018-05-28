<?php

$rdi = new \RecursiveDirectoryIterator(__DIR__ . '/src', \FilesystemIterator::SKIP_DOTS);
$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
$filelist = [];
foreach ($rii as $it) {
    if (!$it->isDir()) {
        $filelist[] = $it->getPathname();
    }
}

return [
    'cachekey'   => md5(implode(',', array_map('md5_file', $filelist))),
    'title'      => 'dbml Class Reference',
    'frontpage'  => 'ryunosuke\\dbml\\',
    'menusize'   => 35,
    'source-map' => [
        '.*/Doctrine/DBAL/' => 'https://github.com/doctrine/dbal/blob/v2.5.13/lib/Doctrine/DBAL/',
        '.*/'               => 'https://github.com/arima-ryunosuke/dbml/blob/master/src/dbml/',
    ],
];
