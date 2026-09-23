<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function __invoke(): View
    {
        return view('home', ['store' => config('storefront')]);
    }

    public function about(): View
    {
        return view('about', ['store' => config('storefront')]);
    }

    public function auth(): View
    {
        return view('auth', ['store' => config('storefront')]);
    }
}
