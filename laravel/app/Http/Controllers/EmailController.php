<?php
// app/Http/Controllers/EmailController.php

namespace App\Http\Controllers;

use App\Services\EmailSyncService;
use Illuminate\Http\Request;
use Elasticsearch\Client;
use App\Models\User;
use GuzzleHttp\Client as GuzzleClient;

class EmailController extends Controller
{
    protected $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function createAccount(Request $request)
    {
        return response()->json(['authUrl' => url('auth/redirect')]);
    }

    /*public function syncEmails(User $user)
    {
        $accessToken = json_decode($user->access_token, true);

        $client = new GuzzleClient([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken['access_token'],
                'Accept' => 'application/json',
            ],
        ]);

        $response = $client->get('me/messages');
        $emails = json_decode($response->getBody(), true);

        foreach ($emails['value'] as $email) {
            $this->elasticsearch->index([
                'index' => 'emails',
                'id'    => $email['id'],
                'body'  => [
                    'user_id' => $user->id,
                    'email'   => $email,
                ],
            ]);
        }

        return response()->json(['status' => 'success']);
    }*/
    public function syncData()
    {
        $user = Auth::user();
        $emailSyncService = new EmailSyncService($user->outlook_token);
        $emailSyncService->syncEmails($user);

        return response()->json(['status' => 'Synchronization in progress']);
    }
}
