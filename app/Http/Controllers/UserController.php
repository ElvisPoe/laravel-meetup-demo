<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function show(User $user): View
    {
        $user->load([
            'ownedProjects',
            'projectMemberships.project',
            'assignedTasks' => fn ($query) => $query
                ->with('project')
                ->orderByRaw("case status when 'todo' then 1 when 'in_progress' then 2 else 3 end")
                ->orderBy('id'),
        ]);

        return view('users.show', [
            'user' => $user,
        ]);
    }
}
