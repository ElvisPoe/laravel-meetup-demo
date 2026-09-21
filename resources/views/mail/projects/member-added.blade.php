<x-mail::message>
# You've been added to {{ $member->project->name }}

Hi {{ $member->user->name }},

You can now view the project, create tasks, and leave comments.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
