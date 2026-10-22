<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'type' => ['required', 'string', Rule::in(['hero', 'villain', 'antihero'])],
            'personality' => ['required', 'string', 'min:3', 'max:500'],
            'main_power' => ['required', 'string', 'min:3', 'max:120'],
            'strength' => ['required', 'integer', 'min:0', 'max:100'],
            'intelligence' => ['required', 'integer', 'min:0', 'max:100'],
            'speed' => ['required', 'integer', 'min:0', 'max:100'],
            'universe' => ['required', 'string', 'min:3', 'max:160'],
        ];
    }
}
