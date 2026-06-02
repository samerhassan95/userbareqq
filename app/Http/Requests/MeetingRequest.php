<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i'],
            'meeting_name' => ['required', 'string', 'max:255'],
            'strategy_id'  => ['nullable', 'exists:product_orders,id'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'date'         => ['nullable', 'date'],
            'slot_id'      => ['nullable'],
            'jitsi_url'    => ['nullable', 'string'],
            'status'       => ['nullable', 'string'],
            'notes'        => ['nullable', 'string'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            foreach ($rules as $key => $rule) {
                if (is_array($rule)) {
                    $nonClosures = array_filter($rule, fn($item) => !($item instanceof \Closure));
                    $closures    = array_filter($rule, fn($item) => $item instanceof \Closure);
                    $nonClosures = array_diff($nonClosures, ['required']);
                    $rules[$key] = array_merge(['nullable'], array_values($nonClosures), $closures);
                } else {
                    $rules[$key] = 'nullable|' . str_replace('required|', '', $rule);
                }
            }
        }

        return $rules;
    }
}
