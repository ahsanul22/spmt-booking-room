<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$input = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
// Isolated PostgreSQL test schema only; never run against the development schema.
if (! str_starts_with($input['connection']['search_path'] ?? '', 'foundation_test_')) {
    exit(10);
}
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $input['connection']]);
Illuminate\Support\Facades\DB::purge('pgsql');
Carbon\CarbonImmutable::setTestNow(Carbon\CarbonImmutable::parse('2026-09-27 10:00:00', 'Asia/Jakarta'));
fwrite(STDOUT, "ready\n");
fflush(STDOUT);
try {
    app(App\Services\BookingService::class)->submit(App\Models\User::findOrFail($input['user_id']), $input['room_id'], $input['data'], $input['token']);
    echo "saved\n";
    exit(0);
} catch (Illuminate\Validation\ValidationException $error) {
    echo isset($error->errors()['start_time']) ? "conflict\n" : "invalid\n";
    exit(2);
}
