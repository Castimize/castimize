<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        abort_if(Gate::denies('createCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
            'wp_id' => 'nullable',
            'date_created' => 'nullable',
            'date_modified' => 'nullable',
            'email' => 'required', //|unique:users
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'username' => 'required', //|unique:users
            'avatar' => 'nullable',
            'billing.first_name' => 'nullable',
            'billing.last_name' => 'nullable',
            'billing.company' => 'nullable',
            'billing.address_line1' => 'required',
            'billing.address_line2' => 'nullable',
            'billing.city' => 'required',
            'billing.postal_code' => 'required',
            'billing.country' => 'required',
            'billing.state' => 'nullable',
            'billing.email' => 'nullable',
            'billing.phone' => 'nullable',
            'shipping.first_name' => 'nullable',
            'shipping.last_name' => 'nullable',
            'shipping.company' => 'nullable',
            'shipping.address_line1' => 'required',
            'shipping.address_line2' => 'nullable',
            'shipping.city' => 'required',
            'shipping.postal_code' => 'required',
            'shipping.country' => 'required',
            'shipping.state' => 'nullable',
            'shipping.email' => 'nullable',
            'shipping.phone' => 'nullable',
        ];
    }
}
