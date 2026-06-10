<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $participantName;
    public $temporaryPassword;
    public $teamName;

    /**
     * Create a new message instance.
     */
    public function __construct($participantName, $temporaryPassword, $teamName)
    {
        $this->participantName = $participantName;
        $this->temporaryPassword = $temporaryPassword;
        $this->teamName = $teamName;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->subject('Приглашение в команду CTF')
                    ->markdown('emails.team-invitation')
                    ->with([
                        'participantName' => $this->participantName,
                        'temporaryPassword' => $this->temporaryPassword,
                        'teamName' => $this->teamName,
                    ]);
    }
}