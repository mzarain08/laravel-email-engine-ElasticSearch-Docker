<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OAuthController extends Controller
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => env('OAUTH_CLIENT_ID'),
            'clientSecret'            => env('OAUTH_CLIENT_SECRET'),
            'redirectUri'             => route('oauth.callback'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
            'scopes'                  => ['openid', 'profile', 'email','offline_access', 'Mail.Read', 'Calendars.Read'], // Add scopes here
        ]);
    }

    public function redirectToProvider()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        session(['oauth2state' => $this->provider->getState()]);
        return redirect($authorizationUrl);
    }

    public function handleProviderCallback(Request $request)
    {
        // Check if the received state matches the one stored in the session
        if (empty($request->input('state')) || ($request->input('state') !== session('oauth2state'))) {
            session()->forget('oauth2state');
            return redirect()->route('login')->with('error', 'Invalid state');
        }


        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);

            $resourceOwner = $this->provider->getResourceOwner($accessToken);
            $userDetails = $resourceOwner->toArray();

            $user = User::updateOrCreate(
                ['email' => $userDetails['mail']],
                [
                    'name' => $userDetails['displayName'],
                    'outlook_id' => $userDetails['id'],
                    'access_token' => $accessToken->getToken(),
                ]
            );
            Auth::login($user,t);
            return redirect('/sync-data');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to get access token');
        }
    }

    public function showLogin()
    {
        return view('login');
    }
}
