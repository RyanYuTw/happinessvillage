<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::firstOrCreate(
        ['email' => 'cecyu.tw@gmail.com'],
        [
            'name' => 'Cecyu',
            'password' => Hash::make('cecyu@rs3128590'),
            'email_verified_at' => now(),
        ]
    );

    if ($user->wasRecentlyCreated) {
        echo "User created successfully.\n";
    } else {
        // Update password if user exists
        $user->password = Hash::make('cecyu@rs3128590');
        $user->save();
        echo "User already exists. Password updated.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
