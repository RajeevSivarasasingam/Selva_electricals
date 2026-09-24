<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Admin}';

    protected $description = 'Create a new administrator with a generated password';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $validator = Validator::make(['email' => $email, 'name' => $name], [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $password = Str::password(20);
        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator created.');
        $this->line('Email: '.$email);
        $this->line('Password: '.$password);

        return self::SUCCESS;
    }
}
