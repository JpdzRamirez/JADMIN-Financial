<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
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
}
