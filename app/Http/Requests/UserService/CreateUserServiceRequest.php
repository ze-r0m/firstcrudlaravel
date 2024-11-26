<?php

namespace App\Http\Requests\UserService;

use App\Http\Requests\BaseRequest;

class CreateUserServiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'is_by_agreement' => 'boolean',
            'is_hourly_type' => 'boolean',
            'is_work_type' => 'boolean',
            'is_active' => 'boolean',
            'hourly_payment' => [
                'nullable',
                'numeric',
                'min:0',
                'max:250',
                'required_if:is_hourly_type,true'
            ],
            'work_payment' =>[
                'nullable',
                'numeric',
                'min:0',
                'max:250',
                'required_if:is_work_type,true'
            ],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->is_one_type_selected()) {
                $validator->errors()->add(
                    'general',
                    'At least one payment type must be selected.'
                );
            }
        });
    }

    private function is_one_type_selected(): bool
    {
        $selectCount = (int) $this->boolean('is_by_agreement') +
            (int) $this->boolean('is_hourly_type')  +
            (int) $this->boolean('is_work_type');
        return $selectCount === 1;
    }
}