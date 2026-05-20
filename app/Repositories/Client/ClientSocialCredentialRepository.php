<?php

namespace App\Repositories\Client;

use App\Models\ClientSocialCredential;

class ClientSocialCredentialRepository implements ClientSocialCredentialRepositoryInterface
{
    protected $model;

    public function __construct(ClientSocialCredential $model)
    {
        $this->model = $model;
    }

    public function getAllByClient($clientId)
    {
        return $this->model->where('client_id', $clientId)->get();
    }

    public function findByClientAndPlatform($clientId, $platform)
    {
        return $this->model->where('client_id', $clientId)
            ->where('platform', $platform)
            ->first();
    }

    public function createOrUpdate($clientId, array $data)
    {
        return $this->model->updateOrCreate(
            [
                'client_id' => $clientId,
                'platform' => $data['platform']
            ],
            [
                'username' => $data['username'],
                'password' => $data['password']
            ]
        );
    }

    public function deleteByClientAndPlatform($clientId, $platform)
    {
        return $this->model->where('client_id', $clientId)
            ->where('platform', $platform)
            ->delete();
    }
}
