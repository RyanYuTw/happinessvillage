<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// 修復資料庫編碼
DB::statement('ALTER DATABASE happinessvillage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

// 修復 handbooks 表編碼
DB::statement('ALTER TABLE handbooks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

echo "資料庫編碼已修復為 UTF-8\n";
