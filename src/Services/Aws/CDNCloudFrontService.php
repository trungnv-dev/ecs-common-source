<?php

namespace App\Services\Aws;

use Aws\CloudFront\CloudFrontClient;
use Carbon\Carbon;

class CDNCloudFrontService
{
    protected $cloudFrontClient;
    protected $keyPairId;
    protected $privateKeyPath;
    protected $cloudFrontDomain;

    public function __construct()
    {
        $this->cloudFrontClient = new CloudFrontClient([
            'region' => config('cloudfront.region'),
            'version' => 'latest',
        ]);
        $this->keyPairId = config('cloudfront.key_pair_id');
        $this->privateKeyPath = config('cloudfront.private_key');
        $this->cloudFrontDomain = config('cloudfront.domain');
    }

    public function getSignedUrl($resourcePath, $expiresInMinutes = 10)
    {
        $expires = Carbon::now()->addMinutes($expiresInMinutes)->timestamp;

        return $this->cloudFrontClient->getSignedUrl([
            'url' => $this->cloudFrontDomain . '/' . $resourcePath,
            'expires' => $expires,
            'private_key' => $this->privateKeyPath,
            'key_pair_id' => $this->keyPairId,
        ]);
    }
}
