<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportOldUsers extends Command
{
    protected $signature = 'import:old-users';
    protected $description = 'Import users from old database to new Laravel users table';

    public function handle()
    {
        // Fetch all old users
        $oldUsers = DB::connection('mysql_old')->table('user')->get();

        foreach ($oldUsers as $old) {

            // Insert into new users table
            DB::connection('mysql')->table('users')->insert([
    'role_id'        => 2,
    'name'           => $old->username,
    'email'          => $old->username . '+' . $old->id . '@olduser.com',
    'user_code'      => 'OLD-' . $old->id . '-' . uniqid(),
    'password'       => Hash::make($old->password),
    'created_at'     => now(),
    'updated_at'     => now(),
]);


        }

        $this->info("Migration completed successfully!");
    }
}
