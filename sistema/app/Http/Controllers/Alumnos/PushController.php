<?php

namespace App\Http\Controllers\Alumnos;

use App\Http\Controllers\Controller;
use App\Push\PushMensajeEnviadoRepository;
use App\Push\PushSubscriptionRepository;
use Illuminate\Http\Request;
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

    public function misNotificaciones()
    {
        $userKey = (string) Auth::guard('alumno')->id();
        $mensajes = PushMensajeEnviadoRepository::listarPorUserKey($userKey);

        return view('alumnos.push.mis-notificaciones', [
            'mensajes' => $mensajes,
        ]);
    }

    public function ver(Request $request, int $id)
    {
        $userKey = (string) Auth::guard('alumno')->id();
        $msg = PushMensajeEnviadoRepository::getByIdForUserKey($id, $userKey);
        abort_if($msg === null, 404);

        return view('alumnos.push.ver', [
            'msg' => $msg,
        ]);
    }
}

