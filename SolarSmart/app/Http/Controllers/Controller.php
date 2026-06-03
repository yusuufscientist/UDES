<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function currentUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        return User::where('email', 'fcyusuuf@gmail.com')->first() ?? User::first();
    }

    protected function authorizeOrPass($ability, $arguments = null): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()->all(),
        ], 422);
    }

    public function callAction($method, $parameters)
    {
        if (! method_exists($this, $method)) {
            abort(404);
        }

        return parent::callAction($method, $parameters);
    }
}