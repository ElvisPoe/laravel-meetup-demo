<?php

namespace App\Mail;

use App\Models\ProjectMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberAddedToProjectMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectMember $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You were added to a project',
        );
    }

    public function content(): Content
    {
        $this->member->loadMissing(['user', 'project']);

        return new Content(
            markdown: 'mail.projects.member-added',
        );
    }
}
