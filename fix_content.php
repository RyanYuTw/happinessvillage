<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$handbooks = DB::table('handbooks')->get();

foreach ($handbooks as $handbook) {
    if ($handbook->content && strpos($handbook->content, '&lt;') !== false) {
        $decoded = html_entity_decode($handbook->content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        DB::table('handbooks')
            ->where('id', $handbook->id)
            ->update(['content' => $decoded]);
        echo "修復 ID {$handbook->id}\n";
    }
}

echo "完成\n";
