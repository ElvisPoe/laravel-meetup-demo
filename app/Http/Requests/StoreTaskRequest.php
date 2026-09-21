<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [Task::class, $this->route('project')]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'status' => ['sometimes', Rule::in([TaskStatus::Todo, TaskStatus::InProgress])],
            'due_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('assignee_id') || ! $this->filled('assignee_id')) {
                    return;
                }

                $assignee = User::query()->find($this->integer('assignee_id'));
                $project = $this->route('project');

                if ($assignee === null || ! $assignee->isMemberOf($project)) {
                    $validator->errors()->add('assignee_id', 'The assignee must be a member of this project.');
                }
            },
        ];
    }
}
