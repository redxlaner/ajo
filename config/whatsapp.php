<?php

return [
    'token' => env('WHATSAPP_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'ajo-whatsapp-verify'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'graph_version' => 'v21.0',
];