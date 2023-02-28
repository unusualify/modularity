<?php

namespace Unusual\CRM\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class BaseFormRequest extends FormRequest
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
        // dd(phpversion());

        switch($this->method()){
            case 'POST':
                return $this->store();
                break;
            case 'PUT':
            case 'PATCH':
                return $this->update();
                break;
            case 'DELETE':
                return $this->destroy();
                break;
            default:
                return $this->view();
                break;
        }

        // return match($this->method()){
        //     'POST' => $this->store(),
        //     'PUT', 'PATCH' => $this->update(),
        //     'DELETE' => $this->destroy(),
        //     default => $this->view()
        // };

    }

    /**
     * Get the validation rules that apply to the get request.
     *
     * @return array
     */
    public function view()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation rules that apply to the post request.
     *
     * @return array
     */
    public function store()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation rules that apply to the put/patch request.
     *
     * @return array
     */
    public function update()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation rules that apply to the delete request.
     *
     * @return array
     */
    public function destroy()
    {
        return [
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
       if($this->wantsJson())
       {
           $response = response()->json([
               'status' => 400,
               'errors' => $validator->errors()
           ]);
       }else{
           $response = redirect()
               ->back()
               ->with('message', 'Ops! Some errors occurred')
               ->withErrors($validator);
       }

       throw (new ValidationException($validator, $response))
           ->errorBag($this->errorBag)
           ->redirectTo($this->getRedirectUrl());
    }
}
