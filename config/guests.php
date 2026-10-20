<?php

return [
    // When unset, use the first office from the master data.
    'branch_id' => env('GUEST_BRANCH_ID'),
    // days or end_of_day; end_of_day registrations after 17:00 last until tomorrow.
    'expiry_mode' => env('GUEST_EXPIRY_MODE', 'days'),
    'active_days' => (int) env('GUEST_ACTIVE_DAYS', 3),
];
