<?php

namespace App\Listeners;

use App\Events\MemberAddedToProject;
use App\Mail\MemberAddedToProjectMail;
use Illuminate\Support\Facades\Mail;

class SendMemberAddedMail
{
    public function handle(MemberAddedToProject $event): void
    {
        $member = $event->member->loadMissing(['user', 'project']);

        Mail::to($member->user)->send(
            (new MemberAddedToProjectMail($member))->afterCommit()
        );
    }
}
