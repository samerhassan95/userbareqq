<?php

namespace App\Repositories\Client;

interface ClientSocialCredentialRepositoryInterface
{
    public function getAllByClient($clientId);
    public function findByClientAndPlatform($clientId, $platform);
    public function createOrUpdate($clientId, array $data);
    public function deleteByClientAndPlatform($clientId, $platform);
}
