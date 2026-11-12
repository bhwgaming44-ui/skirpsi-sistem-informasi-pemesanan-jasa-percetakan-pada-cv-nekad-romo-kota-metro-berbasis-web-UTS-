<?php

return [
    'biaya_desain' => (int) env('PERCETAKAN_BIAYA_DESAIN', 25000),
    'maks_upload_kb' => (int) env('PERCETAKAN_MAKS_UPLOAD_KB', 20480),
    'zona_waktu' => env('PERCETAKAN_ZONA_WAKTU', 'Asia/Jakarta'),
];
