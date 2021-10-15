<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reports = ['module_visualization', 'resource_type_usage', 'file_type_usage',
        'interaction_by_resource', 'resource_visualization', 'course_communication',
        'evaluation_panic', 'course_interaction', null];
        $contexts = ['report', 'download', 'report_information'];
        $deeps = ['first_level', 'second_level', 'third_level', null];
        return [
            '*' => 'required|array|min:1',
            '*.course_id' => 'nullable|integer',
            '*.session_id' => 'required|string',
            '*.context' => Rule::in($contexts),
            '*.report' => Rule::in($reports),
            '*.deep' => Rule::in($deeps),
            '*.reference' => 'nullable|string',
            '*.params' => 'nullable|json',
            '*.created_at' => 'date_format:Y-m-d H:i:s'
        ];
    }
}
