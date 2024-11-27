<?php

namespace App\Http\Requests\Portfolio;

use App\Http\Requests\BaseRequest;

class UpdatePortfolioRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
//            'service_name' => 'required|string',
//            'photo' => 'required|string',
//            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description' => 'string|nullable',
            'price' => 'nullable|numeric|min:0|max:250',
        ];

    }
}