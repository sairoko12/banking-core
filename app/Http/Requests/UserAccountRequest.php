<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Repositories\Dictionaries\AccountType;

class UserAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET': case 'HEAD': case 'DELETE':
                return [];
                break;

            case 'POST': default:
                return [
                    'account_type' => [
                        'required',
                        'max:15',
                        Rule::in([AccountType::DEBIT, AccountType::CREDIT])
                    ],
                    'alias' => [
                        'max:100'
                    ]
                ];
                break;
            case 'PUT': case 'PATCH':
                return [
                    'account_type' => [
                        'max:15',
                        Rule::in([AccountType::DEBIT, AccountType::CREDIT])
                    ],
                    'alias' => [
                        'max:100'
                    ]
                ];
                break;
        }

    }
}
