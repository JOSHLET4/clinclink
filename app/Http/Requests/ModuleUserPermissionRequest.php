<?php

namespace App\Http\Requests;

use App\Models\ModuleUserPermission;
use App\Rules\UniqueCombinationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuleUserPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'min:1',
                new UniqueCombinationRule(
                    'module_user_permissions',
                    $this->route('module_user_permission'),
                    [
                        'user_id' => $this->user_id,
                        'module_id' => $this->module_id,
                        'permission_id' => $this->permission_id
                    ]
                )
            ],
            // 'user_id' => 'required|min:1|integer',
            'module_id' => 'required|min:1|integer',
            'permission_id' => 'required|min:1|integer',
        ];
    }
}
