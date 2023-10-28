<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function showChangePasswordForm()
    {
        return view('change-password.index');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|password',
            'password' => 'min:6|required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect('/');
    }
}
