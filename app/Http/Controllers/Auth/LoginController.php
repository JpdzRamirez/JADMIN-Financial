<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function inicio(){

        return view('auth.login');
    }   

    public function login(Request $request)
    {
        $credenciales =    $this->validate(request(), [
            'usuario' => 'required|string',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($credenciales)) {
            if(Auth::user()->estado == 1){
                if(Auth::user()->rol == 2){
                    return redirect('/mis_creditos');
                }elseif(Auth::user()->rol == 3){
                    return redirect('/pagos/registrar');
                }elseif(Auth::user()->rol == 0){
                    return redirect('/creditos_cobro');
                }elseif(Auth::user()->rol == 1){
                    return redirect('/creditos_cobro');
                }elseif (Auth::user()->rol == 4) {
                    return redirect('/contabilidad/plan_cuentas');
                }    
            }else{
                Auth::logout();
                return back()
                ->withErrors(['usuario' => 'El usuario ingresado se encuentra inactivo'])
                ->withInput(request(['usuario']));
            }        
        } else {
            return back()
                ->withErrors(['usuario' => trans('auth.failed')])
                ->withInput(request(['usuario']));
        }
    }

    public function logout()
    {

        Auth::logout();
        Session::flush();

        return redirect('/');
    }

    public function redirectTo()
    {
        Session::flush();
        if(Auth::check()){
            if(Auth::user()->rol == 2){
                return redirect('/mis_creditos');
            }elseif(Auth::user()->rol == 3){
                return redirect('/pagos/registrar');
            }elseif(Auth::user()->rol == 1){
                return redirect('/creditos_cobro');
            }elseif (Auth::user()->rol == 4) {
                return redirect('/contabilidad/plan_cuentas');
            }     
        }         
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
