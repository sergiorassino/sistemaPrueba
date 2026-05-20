<?php

namespace App\Http\Controllers\Push;

use App\Http\Controllers\Controller;
use App\Push\PushSubscriptionRepository;
use App\Push\PushUserKey;

class SuscribirController extends Controller
{
    public function __invoke()
    {
        abort_unless(tienePermiso(14), 403, 'Sin permiso para el módulo de configuración.');

        $userKey = PushUserKey::forAuthenticatedUser();

        return view('push.suscribir', [
            'hasSubscription' => PushSubscriptionRepository::hasAnyForUserKey($userKey),
        ]);
    }
}
