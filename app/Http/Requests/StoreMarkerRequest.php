<?php

namespace App\Http\Requests;

use App\Models\Map;
use App\Models\Marker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreMarkerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Map $map)
    {
        return Gate::allows('create', [Marker::class, $this->map, $this->map_token]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category' => 'required_without:category_name|exists:categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'description' => ['nullable', 'string', 'max:191', new \App\Rules\NotContainsString()],
            'category_name' => ['required_without:category', 'min:3', 'max:32', new \App\Rules\NotContainsString()],
            'link' => [
                Rule::requiredIf(optional($this->map->options)['links'] === "required"),
                Rule::prohibitedIf(optional($this->map->options)['links'] === "disabled"),
                'nullable',
                'url'
            ],
            'elevation' => 'nullable|numeric|between:-100000,100000',
            'zoom' => 'nullable|numeric|between:0,20',
            "expires_at" => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
}
