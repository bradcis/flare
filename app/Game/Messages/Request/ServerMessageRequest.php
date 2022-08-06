<?php

namespace App\Game\Messages\Request;

use Illuminate\Foundation\Http\FormRequest;

class ServerMessageRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            'custom_message' => 'nullable|string',
            'type'           => 'nullable|string',
        ];
    }
}
