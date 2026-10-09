<?php

namespace App\Console\Commands;

use App\Mail\IndependenceDay;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendIndependenceDay extends Command
{
    protected $signature = 'ajo:independence {--test=}';
    protected $description = 'Send Independence Day email to users';

    public function handle(): int
    {
        if ($to = $this->option('test')) {
            Mail::to($to)->send(new IndependenceDay());
            $this->info('Test sent to '.$to);
            return self::SUCCESS;
        }

        User::whereNotNull('email')->orderBy('id')->chunk(50, function ($users) {
            foreach ($users as $user) {
                Mail::to($user->email)->send(new IndependenceDay());
                $this->line($user->email);
            }
        });

        $this->info('Done.');
        return self::SUCCESS;
    }
}