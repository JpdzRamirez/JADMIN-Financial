<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if(Auth::user()->estado == 1){
                    if(Auth::user()->rol == 2){
                        return redirect('/mis_creditos');
                    }elseif(Auth::user()->rol == 3){
                        return redirect('/pagos/registrar');
                    }elseif(Auth::user()->rol == 1){
                        return redirect('/creditos_cobro');
                    }elseif (Auth::user()->rol == 4) {
                        return redirect('/contabilidad/plan_cuentas');
                    }                  
                }else{
                    Auth::logout();
                    Session::flush();
                    return redirect('/')
                    ->withErrors(['usuario' => 'El usuario ingresado se encuentra inactivo'])
                    ->withInput(request(['usuario']));
                }
            }
        }

        return $next($request);
    }
}
