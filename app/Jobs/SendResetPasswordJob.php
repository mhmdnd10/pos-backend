<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;

class SendResetPasswordJob implements ShouldQueue
{
    use Dispatchable, Queueable,InteractsWithQueue, SerializesModels;
    public $email;
    public $code;
    public $tries = 3;

     /**
     * Create a new job instance.
     */

    /**
     * Create a new job instance.
     */
    public function __construct($email, $code)
     {
        $this->email = $email;
        $this->code = $code;
     }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new ResetPasswordMail($this->email, $this->code));
    }
}
 