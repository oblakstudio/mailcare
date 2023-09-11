<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Email;

class FavoritesController extends Controller
{
    public function store(Email $email)
    {
        if ($email->favorite) {
            return;
        }

        $email->favorite = true;
        $email->save();
    }

    public function destroy(Email $email)
    {
        if (!$email->favorite) {
            return;
        }

        $email->favorite = false;
        $email->save();
    }
}
