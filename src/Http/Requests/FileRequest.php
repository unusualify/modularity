<?php

namespace OoBook\CRM\Base\Http\Requests;

use OoBook\CRM\Base\Http\Requests\BaseFormRequest;

class FileRequest extends BaseFormRequest
{
    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    // public function rules()
    // {
    //     switch (config('twill.file_library.endpoint_type')) {
    //         case 'local':
    //             return [
    //                 'qqfilename' => 'required',
    //                 'qqfile' => 'required',
    //                 'qqtotalfilesize' => 'required',
    //             ];
    //         case 'azure':
    //             return [
    //                 'blob' => 'required',
    //                 'name' => 'required',
    //             ];
    //         case 's3':
    //         default:
    //             return [
    //                 'key' => 'required',
    //                 'name' => 'required',
    //             ];
    //     }
    // }

    public function authorize()
    {
        return true;
    }
}
