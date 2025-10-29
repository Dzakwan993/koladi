<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $inviter;

    public function __construct(Invitation $invitation, User $inviter)
    {
        $this->invitation = $invitation;
        $this->inviter = $inviter;
    }

    public function build()
    {
        $url = url('/invite/accept/' . $this->invitation->token);

        return $this->subject('Undangan Bergabung ke Koladi')
            ->view('emails.invitation')
            ->with([
                'inviterName' => $this->inviter->full_name,
                'companyName' => $this->invitation->company->name ?? 'Perusahaan',
                'url' => $url,
                // 🔥 TAMBAHKAN ini untuk backward compatibility
                'inviter' => $this->inviter->full_name,
                'company' => $this->invitation->company->name ?? 'Perusahaan',
            ]);
    }
}
