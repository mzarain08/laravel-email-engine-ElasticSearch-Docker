<?php

namespace App\Services;

use GuzzleHttp\Client;
use Elastic\Elasticsearch\ClientBuilder;
use App\Models\Email;
use App\Models\User;

class EmailSyncService
{
    private $client;
    private $esClient;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://graph.microsoft.com/v1.0/']);
        $this->esClient = ClientBuilder::create()->build();
    }

    public function syncEmails(User $user, $accessToken)
    {
        $response = $this->client->request('GET', 'me/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ]);

        $emails = json_decode($response->getBody()->getContents(), true);

        foreach ($emails['value'] as $email) {
            $emailRecord = Email::updateOrCreate(
                ['email_id' => $email['id']],
                [
                    'user_id' => $user->id,
                    'subject' => $email['subject'],
                    'sender' => $email['from']['emailAddress']['address'],
                    'body' => $email['body']['content'],
                    'received_at' => $email['receivedDateTime']
                ]
            );

            $params = [
                'index' => 'emails',
                'id' => $email['id'],
                'body' => [
                    'user_id' => $user->id,
                    'email_id' => $email['id'],
                    'subject' => $email['subject'],
                    'sender' => $email['from']['emailAddress']['address'],
                    'body' => $email['body']['content'],
                    'timestamp' => $email['receivedDateTime']
                ]
            ];
            $this->esClient->index($params);
        }
    }
}
