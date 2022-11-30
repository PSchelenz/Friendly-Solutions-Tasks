<?php

namespace Modules\Importer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HtmlFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'html_file' => 'file|mimes:html,txt',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
