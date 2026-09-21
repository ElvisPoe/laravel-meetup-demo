<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with([
                'owner',
                'members.user',
                'tasks' => fn ($query) => $query
                    ->with('assignee')
                    ->orderByRaw("case status when 'todo' then 1 when 'in_progress' then 2 else 3 end")
                    ->orderBy('id'),
            ])
            ->latest('id')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }
}
