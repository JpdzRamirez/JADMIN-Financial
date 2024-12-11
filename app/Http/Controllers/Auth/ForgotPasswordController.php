<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirectTo()
    {
          
    }
    
    public function sendResetLinkEmail(Request $request)
    {
        $user = User::where('usuario', $request->input('usuario'))->first();
        if($user != null){
            if($user->roles_id == 3 || $user->roles_id == 4){
                $from = "cris@taxiseguro.tax";
                if($user->roles_id == 3){
                    $to = $user->sucursal->tercero->EMAIL;   
                }else{
                    $to = $user->cuentae->agencia->EMAIL;
                }
                $to = "cristianieto21@gmail.com";
                $subject = "Restablecimiento de contraseña";
                $mensaje = "Se ha registrado un intento por restablecer su contraseña \n\n" .
                            "Usuario: " . $user->usuario . "\n" . 
                            
                            "Para continuar con el proceso de restablecimiento, use el siguiente enlace. \n\n" .
                            "http://crm.apptaxcenter.com/restablecer/" . $user->usuario . "/" . md5($user->usuario);
                $headers = "From:" . $from;
                
                Mail::send([], [], function ($message) use($to, $subject, $mensaje, $from){
                    $message->from($from, "Taxiseguro");
                    $message->to($to);
                    $message->subject($subject);
                    $message->setBody($mensaje, 'text/html');
                });
                
                return back()->withErrors(["bien" => "Se ha enviado un enlace de restablecimiento a " . $to]);

            }else{
                return back()->withErrors(["sql" => "El usuario ingresado no tiene un correo electrónico registrado"]);
            }
        }else{
            return back()->withErrors(["sql" => "El usuario ingresado no se encuentra"]);
        }
        
    }
}