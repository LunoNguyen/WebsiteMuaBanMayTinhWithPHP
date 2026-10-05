<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MinIO (lưu ảnh sản phẩm)
    |--------------------------------------------------------------------------
    |
    | Ảnh tải lên được lưu trên MinIO qua API tương thích S3. Bucket để riêng
    | tư; trình duyệt xem ảnh qua route /anh/... của website (AnhController).
    |
    */

    'endpoint' => env('MINIO_ENDPOINT', 'http://localhost:9000'),
    'access_key' => env('MINIO_ACCESS_KEY'),
    'secret_key' => env('MINIO_SECRET_KEY'),
    'bucket' => env('MINIO_BUCKET', 'ban-may-tinh'),
    'region' => env('MINIO_REGION', 'us-east-1'),

    'max_kb' => 5 * 1024,
    'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],

];
