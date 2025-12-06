<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$page = App\Models\Page::find(7);
if ($page) {
    echo "Page found: " . $page->title . "\n\n";
    echo "Content JSON:\n";
    echo json_encode($page->content, JSON_PRETTY_PRINT);
}
