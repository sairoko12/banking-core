<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Repositories\Dictionaries\SourceTypeDeposit;

class AccountDepositRequest extends FormRequest
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
        return [
            'source' => [
                'required',
                'max:150',
                Rule::in([
                    SourceTypeDeposit::BANK_DEPOSIT,
                    SourceTypeDeposit::SPEI,
                    SourceTypeDeposit::STORE_DEPOSIT,
                    SourceTypeDeposit::CREDIT_PAYMENT
                ])
            ],
            'operation_date' => [
                'required',
                'date_format:Y-m-d'
            ],
            'liquidation_date' => [
                'required',
                'date_format:Y-m-d'
            ],
            'description' => [
                'max:150'
            ],
            'amount' => [
                'required',
                'numeric'
            ]
        ];
    }
}
