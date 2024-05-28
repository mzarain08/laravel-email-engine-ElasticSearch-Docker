<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailSyncService;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    public function createAccount(Request $request)
    {
        return response()->json(['authUrl' => url('auth/redirect')]);
    }

    public function syncData()
    {
        $user = Auth::user();
        $emailSyncService = new EmailSyncService();
        $emailSyncService->syncEmails($user, $user->outlook_token);

        return response()->json(['status' => 'Synchronization in progress']);
    }
}
