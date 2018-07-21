<?php

$rdi = new \RecursiveDirectoryIterator(__DIR__ . '/src', \FilesystemIterator::SKIP_DOTS);
$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
$filelist = [];
foreach ($rii as $it) {
    if (!$it->isDir()) {
        $filelist[] = $it->getPathname();
    }
}

$lock = json_decode(file_get_contents(__DIR__ . '/composer.lock'), true);
$version = array_column($lock['packages'], 'version', 'name');

return [
    'cachekey'   => md5(implode(',', array_map('md5_file', $filelist))),
    'title'      => 'dbml Class Reference',
    'frontpage'  => 'ryunosuke\\dbml\\',
    'menusize'   => 35,
    'source-map' => [
        '.*/Doctrine/Common/(Event.*)' => function ($m) use ($version) {
            return "https://github.com/doctrine/event-manager/blob/{$version['doctrine/event-manager']}/lib/Doctrine/Common/" . $m[1];
        },
        '.*/Doctrine/Common/Cache/'    => function ($m) use ($version) {
            return "https://github.com/doctrine/cache/blob/{$version['doctrine/cache']}/lib/Doctrine/Common/Cache/";
        },
        '.*/Doctrine/DBAL/'            => function ($m) use ($version) {
            return "https://github.com/doctrine/dbal/blob/{$version['doctrine/dbal']}/lib/Doctrine/DBAL/";
        },
        '.*/dbml/'                     => 'https://github.com/arima-ryunosuke/dbml/blob/master/src/dbml/',
        '.*'                           => '',
    ],
];
