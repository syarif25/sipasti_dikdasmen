<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = auth()->loginUsingId(auth()->user() ? auth()->id() : 1); // just get first user or something
$lembagas = \App\Models\Lembaga::limit(5)->get();
echo json_encode($lembagas);
