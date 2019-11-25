<?php

namespace App\Http\Controllers;

use App\Jobs\ExampleJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getUser(Request $request)
    {
        return response()->json(['user' => Auth::user()]);
    }

    public function test2(Request $request)
    {
        $user = User::find(1);
        // dispatch(new ExampleJob($user));
        Queue::push(new ExampleJob($user));
    }
}
