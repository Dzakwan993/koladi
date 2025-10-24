<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberRemovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $company;
    public $removedBy;

    public function __construct(User $user, Company $company, User $removedBy)
    {
        $this->user = $user;
        $this->company = $company;
        $this->removedBy = $removedBy;
    }

    public function build()
    {
        return $this->subject('Akses Anda ke ' . $this->company->name . ' Telah Dicabut')
            ->view('emails.member-removed');
    }
}
