<?php

namespace Energon7\MenuBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Energon7\MenuBuilder\MenuBuilder;

class NewMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return resolve(MenuBuilder::class)->authorize(request()) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       \Validator::extend('uniqueTwoColumns', function ($attribute, $value, $parameters, $validator)  {

            $count = \DB::table('menu_items')
                ->where('menu_id', request()->get('menu_id'))
                ->where('id','<>',$parameters[1])
                ->where('page_id', $value)
                ->count();
            return $count === 0;
        });
        $rules = [
            'menu_id' => 'required|exists:menus,id',
            'name'    => 'required', //
            'page_id' => 'nullable|uniqueTwoColumns:'.request()->get('page_id').','.request()->get('id'),
            'type'    => 'required|in:link,route',
            'target'  => 'required|in:_self,_blank',
        ];
		
        if (request()->get('type') == 'link') {
            $rules['url'] = 'required';
        }

        if (request()->get('type') == 'route') {
            $rules['route'] = [
                'required',
                function ($attribute, $value, $fail) {
                    if (Route::has($value)) {
                        return true;
                    }

                    return $fail(ucfirst($attribute).' not is a real route name');
                },
            ];
            // $rules['parameters'] = 'required';
        }

        return $rules;
    }
}
