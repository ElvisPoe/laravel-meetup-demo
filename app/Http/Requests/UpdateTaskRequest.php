<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('task')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
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
                if ($validator->errors()->has('assignee_id') || ! $this->exists('assignee_id') || $this->input('assignee_id') === null) {
                    return;
                }

                $assignee = User::query()->find($this->integer('assignee_id'));
                $project = $this->route('project') ?? $this->route('task')?->project()->first();

                if ($assignee === null || $project === null || ! $assignee->isMemberOf($project)) {
                    $validator->errors()->add('assignee_id', 'The assignee must be a member of this project.');
                }
            },
        ];
    }
}
