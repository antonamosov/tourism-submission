<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
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
        $rules = [
            'users' => 'required|array',
        ];

        foreach($this->request->get('users') as $key => $user) {
            $rules['users.'. $key . '.name'] = 'required|string|min:3|max:255';
            $rules['users.'. $key . '.phone'] = 'required|regex:/^[0-9\+\s]+$/|min:10|max:17';
            $rules['users.'. $key . '.country'] = 'required|string|min:2:max:3';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        foreach($this->request->get('users') as $key => $val)
        {
            $messages['users.'. $key . '.name.max'] = 'The field labeled "Name ' . $key . '" must be less than :max characters.';
            $messages['users.'. $key . '.phone.max'] = 'The field labeled "Phone ' . $key . '" must be less than :max characters.';
            $messages['users.'. $key . '.name.min'] = 'The field labeled "Name ' . $key . '" must be more than :min characters.';
            $messages['users.'. $key . '.phone.min'] = 'The field labeled "Phone ' . $key . '" must be more than :min characters.';
        }
        return $messages;
    }
}
