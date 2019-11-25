<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExampleJob extends Job
{
    public $connection = 'redis';

    public $tries = 10;

    public $delay = 10;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::create([
            'username' => Str::random(12),
            'password' => Hash::make('a123456'),
        ]);
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        // return Carbon::now()->addSeconds(5);
        return Carbon::now()->addMinutes(3);
    }
}
