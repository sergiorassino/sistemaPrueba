<?php

namespace App\Http\Controllers\Alumnos;

use App\Http\Controllers\Controller;
use App\Push\PushSubscriptionRepository;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function index()
    {
        $userKey = (string) Auth::guard('alumno')->id();
        $hasSubscription = PushSubscriptionRepository::hasAnyForUserKey($userKey);

        return view('alumnos.push.index', [
            'hasSubscription' => $hasSubscription,
        ]);
    }
}
