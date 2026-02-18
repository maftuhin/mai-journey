<?php

return [
    // Max upload size accepted by validation (KB). 10240 = 10MB.
    'max_upload_kb' => (int) env('GALLERY_MAX_UPLOAD_KB', 10240),

    // Max final saved image size after compression (bytes). 1048576 = 1MB.
    'max_saved_bytes' => (int) env('GALLERY_MAX_SAVED_BYTES', 1048576),
];

