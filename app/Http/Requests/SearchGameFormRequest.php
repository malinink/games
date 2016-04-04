<?php
/**
 *
 * @author Ananaskelly
 */
namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\GameType;

class SearchGameFormRequest extends Request
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
        $min = GameType::all()->min('id');
        $max = GameType::all()->max('id');
        return [
            'status' => 'required|boolean',
            'type'   => "required|integer|between:$min,$max"
        ];
    }
}
