<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class PromoteAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promote:admin {identifier=admin : username or email of user to promote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote a user to admin and set name to Administrator';

    public function handle()
    {
        $id = $this->argument('identifier');

        $user = User::where('username', $id)->orWhere('email', $id)->first();
        if (! $user) {
            $this->error("User not found for identifier: {$id}");
            return 1;
        }

        $user->update([
            'is_admin' => true,
            'name' => 'Administrator',
        ]);

        $this->info("User {$user->username} promoted to admin and name set to Administrator.");
        return 0;
    }
}
