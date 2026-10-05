<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Lưu file trên MinIO qua API tương thích S3, ký request bằng AWS Signature V4.
 */
class MinioStorage
{
    public function __construct(
        private string $endpoint,
        private string $accessKey,
        private string $secretKey,
        private string $bucket,
        private string $region,
        private int $urlTtl,
    ) {}

    /**
     * Tạo service từ config/minio.php.
     */
    public static function fromConfig(): self
    {
        return new self(
            endpoint: rtrim((string) config('minio.endpoint'), '/'),
            accessKey: (string) config('minio.access_key'),
            secretKey: (string) config('minio.secret_key'),
            bucket: (string) config('minio.bucket'),
            region: (string) config('minio.region'),
            urlTtl: (int) config('minio.url_ttl'),
        );
    }

    /**
     * Tải một file lên dưới thư mục $folder. Trả về khoá object, hoặc null khi lỗi.
     */
    public function upload(UploadedFile $file, string $folder): ?string
    {
        $folder = trim((string) preg_replace('#[^A-Za-z0-9/_-]#', '', $folder), '/');
        $key = $folder.'/'.now()->format('Ymd-His').'-'.Str::lower(Str::random(8)).'.'.$file->extension();

        return $this->put($key, (string) $file->get(), (string) $file->getMimeType()) ? $key : null;
    }

    /**
     * Ghi nội dung lên MinIO, tạo bucket nếu chưa có.
     */
    public function put(string $key, string $content, string $contentType = 'application/octet-stream'): bool
    {
        $response = $this->request('PUT', $key, $content, ['content-type' => $contentType]);

        if ($response?->status() === 404 && $this->ensureBucket()) {
            $response = $this->request('PUT', $key, $content, ['content-type' => $contentType]);
        }

        if ($response?->successful() !== true) {
            Log::warning('MinIO PUT thất bại', ['key' => $key, 'status' => $response?->status()]);

            return false;
        }

        return true;
    }

    /**
     * Xoá một object.
     */
    public function delete(string $key): bool
    {
        return $this->request('DELETE', $key)?->successful() === true;
    }

    /**
     * Object có tồn tại không.
     */
    public function exists(string $key): bool
    {
        return $this->request('HEAD', $key)?->status() === 200;
    }

    /**
     * Tạo bucket nếu chưa có.
     */
    public function ensureBucket(): bool
    {
        $head = $this->request('HEAD');

        if ($head?->status() === 200) {
            return true;
        }

        return $head?->status() === 404 && $this->request('PUT')?->successful() === true;
    }

    /**
     * Link xem file có thời hạn (presigned GET). Khoá rỗng trả về chuỗi rỗng; URL ngoài giữ nguyên.
     */
    public function url(?string $key, ?int $ttl = null): string
    {
        if ($key === null || $key === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $key)) {
            return $key;
        }

        $path = '/'.$this->bucket.'/'.$this->encodeKey($key);
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $scope = "{$date}/{$this->region}/s3/aws4_request";

        $query = [
            'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $this->accessKey.'/'.$scope,
            'X-Amz-Date' => $now,
            'X-Amz-Expires' => (string) ($ttl ?? $this->urlTtl),
            'X-Amz-SignedHeaders' => 'host',
        ];
        ksort($query);
        $canonicalQuery = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $canonicalRequest = "GET\n{$path}\n{$canonicalQuery}\nhost:{$this->host()}\n\nhost\nUNSIGNED-PAYLOAD";
        $signature = $this->sign($now, $scope, $canonicalRequest);

        return $this->endpoint.$path.'?'.$canonicalQuery.'&X-Amz-Signature='.$signature;
    }

    /**
     * Gửi một request đã ký tới MinIO. Trả về null khi không kết nối được.
     *
     * @param  array<string, string>  $headers
     */
    private function request(string $method, string $key = '', string $body = '', array $headers = []): ?Response
    {
        $path = '/'.$this->bucket.($key !== '' ? '/'.$this->encodeKey($key) : '');
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $scope = "{$date}/{$this->region}/s3/aws4_request";
        $payloadHash = hash('sha256', $body);

        $headers = array_change_key_case($headers, CASE_LOWER) + [
            'host' => $this->host(),
            'x-amz-date' => $now,
            'x-amz-content-sha256' => $payloadHash,
        ];
        ksort($headers);

        $canonicalHeaders = '';
        foreach ($headers as $name => $value) {
            $canonicalHeaders .= $name.':'.trim($value)."\n";
        }
        $signedHeaders = implode(';', array_keys($headers));
        $canonicalRequest = implode("\n", [$method, $path, '', $canonicalHeaders, $signedHeaders, $payloadHash]);

        $headers['authorization'] = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$scope}, "
            ."SignedHeaders={$signedHeaders}, Signature=".$this->sign($now, $scope, $canonicalRequest);
        unset($headers['host']);

        try {
            return Http::withHeaders($headers)
                ->withBody($body, $headers['content-type'] ?? 'application/octet-stream')
                ->connectTimeout(3)
                ->timeout(30)
                ->send($method, $this->endpoint.$path);
        } catch (Throwable $exception) {
            Log::warning('MinIO không kết nối được', ['error' => $exception->getMessage()]);

            return null;
        }
    }

    private function sign(string $now, string $scope, string $canonicalRequest): string
    {
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonicalRequest);

        $key = hash_hmac('sha256', substr($now, 0, 8), 'AWS4'.$this->secretKey, true);
        $key = hash_hmac('sha256', $this->region, $key, true);
        $key = hash_hmac('sha256', 's3', $key, true);
        $key = hash_hmac('sha256', 'aws4_request', $key, true);

        return hash_hmac('sha256', $stringToSign, $key);
    }

    private function host(): string
    {
        $port = parse_url($this->endpoint, PHP_URL_PORT);

        return parse_url($this->endpoint, PHP_URL_HOST).($port ? ':'.$port : '');
    }

    private function encodeKey(string $key): string
    {
        return implode('/', array_map('rawurlencode', explode('/', ltrim($key, '/'))));
    }
}
