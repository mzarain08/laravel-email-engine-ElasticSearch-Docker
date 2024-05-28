<?php

// app/Console/Commands/SyncEmails.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\EmailSyncService;

class SyncEmails extends Command
{
    protected $signature = 'sync:emails';
    protected $description = 'Synchronize emails from Outlook to local database';
    protected $emailSyncService;

    public function __construct(EmailSyncService $emailSyncService)
    {
        parent::__construct();
        $this->emailSyncService = $emailSyncService;
    }

    public function handle()
    {
        $users = User::whereNotNull('access_token')->get();

        foreach ($users as $user) {
            $this->emailSyncService->sync($user);
        }

        $this->info('Email synchronization complete.');
    }
}
