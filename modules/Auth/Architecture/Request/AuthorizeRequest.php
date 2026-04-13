<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorizeRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'authTicket' => ['required', 'string'],
            'response_type' => [
                'required',
                'string',
                Rule::in(['code']), // must be "code"
            ],
            'client_id' => [
                'required',
                'string',
            ],
            'redirect_uri' => [
                'nullable',
                'url',
            ],
            'scope' => [
                'string',
            ],
            'state' => [
                'nullable',
                'string',
            ],
        ];
    }
}
