<?php

namespace App\Services\Aws;

use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDOException;

class SecretsManagerPasswordService
{
    protected $cache;
    protected $client;

    public function __construct()
    {
        $this->cache = Cache::store('file');
        $this->client = new SecretsManagerClient([
            'version' => 'latest',
            'region' => config('database.connections.mysql.rds_region')
        ]);
    }

    public function ensureConnection()
    {
        try {
            // Set default password form cache
            config(['database.connections.mysql.password' => $this->cache->get('rds_password')]);

            DB::connection()->getPdo(); // connect check
        } catch (PDOException $e) {
            if ($this->isInvalidPasswordError($e)) {
                $this->refreshConnection(); // refresh connect
                DB::connection()->getPdo(); // reconnect check
            } else {
                throw $e;
            }
        }
    }

    public function refreshConnection()
    {
        // Get new password from Secrets Manager
        $newPassword = $this->getPassword(true); // force refresh cache

        if ($newPassword) {
            // Update password in database configuration
            config(['database.connections.mysql.password' => $newPassword]);
            // Reconnect database
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }

    public function getPassword($forceRefresh = false)
    {
        // If $forceRefresh is true, clear cache to get latest password
        if ($forceRefresh) {
            $this->cache->forget('rds_password');
        }

        $ttl = 60 * 60 * 24; // 1 day

        return $this->cache->remember('rds_password', $ttl, function () {
                try {
                    $result = $this->client->getSecretValue([
                        'SecretId' => config('database.connections.mysql.rds_secret')
                    ]);
        
                    return json_decode($result['SecretString'], true)['password'] ?? null;
                } catch (AwsException $e) {
                    report($e);
                    return null;
                }
            });
    }

    protected function isInvalidPasswordError(PDOException $e)
    {
        return $e->getCode() == 1045 && strpos($e->getMessage(), 'Access denied for user') !== false;
    }
}