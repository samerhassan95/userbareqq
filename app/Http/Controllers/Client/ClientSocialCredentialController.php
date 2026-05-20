<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreSocialCredentialRequest;
use App\Http\Resources\ClientSocialCredentialResource;
use App\Repositories\Client\ClientSocialCredentialRepositoryInterface;
use Illuminate\Http\Request;

class ClientSocialCredentialController extends Controller
{
    protected $credentialRepository;

    public function __construct(ClientSocialCredentialRepositoryInterface $credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
    }

    /**
     * Get all credentials for authenticated client
     * GET /api/client/credentials
     */
    public function index(Request $request)
    {
        $client = auth()->user();
        
        $credentials = $this->credentialRepository->getAllByClient($client->id);
        
        return ResponseHelper::success(
            ClientSocialCredentialResource::collection($credentials),
            __('messages.list_success')
        );
    }

    /**
     * Store or update a credential
     * POST /api/client/credentials
     */
    public function store(StoreSocialCredentialRequest $request)
    {
        $client = auth()->user();
        
        $credential = $this->credentialRepository->createOrUpdate(
            $client->id,
            $request->validated()
        );
        
        return ResponseHelper::success(
            new ClientSocialCredentialResource($credential),
            __('Credential saved successfully'),
            201
        );
    }

    /**
     * Update a specific platform credential
     * PUT /api/client/credentials/{platform}
     */
    public function update(StoreSocialCredentialRequest $request, $platform)
    {
        $client = auth()->user();
        
        // Validate platform exists in enum
        $validPlatforms = ['facebook', 'tiktok', 'instagram', 'linkedin', 'twitter'];
        if (!in_array($platform, $validPlatforms)) {
            return ResponseHelper::error(__('Invalid platform'), [], 404);
        }
        
        $data = $request->validated();
        $data['platform'] = $platform; // Override with URL parameter
        
        $credential = $this->credentialRepository->createOrUpdate(
            $client->id,
            $data
        );
        
        return ResponseHelper::success(
            new ClientSocialCredentialResource($credential),
            __('Credential updated successfully')
        );
    }

    /**
     * Delete a platform credential
     * DELETE /api/client/credentials/{platform}
     */
    public function destroy(Request $request, $platform)
    {
        $client = auth()->user();
        
        // Validate platform exists in enum
        $validPlatforms = ['facebook', 'tiktok', 'instagram', 'linkedin', 'twitter'];
        if (!in_array($platform, $validPlatforms)) {
            return ResponseHelper::error(__('Invalid platform'), [], 404);
        }
        
        $deleted = $this->credentialRepository->deleteByClientAndPlatform($client->id, $platform);
        
        if ($deleted) {
            return ResponseHelper::success(
                null,
                __('Credential deleted successfully')
            );
        }
        
        return ResponseHelper::success(
            null,
            __('Credential not found or already deleted')
        );
    }
}
