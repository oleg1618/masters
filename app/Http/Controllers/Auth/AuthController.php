<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
//use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Auth, Lang, Mail;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use /*AuthenticatesAndRegistersUsers, */ThrottlesLogins;
    use AuthenticatesUsers, RegistersUsers {
        AuthenticatesUsers::redirectPath insteadof RegistersUsers;
        AuthenticatesUsers::getGuard insteadof RegistersUsers;
        AuthenticatesUsers::login as parentLogin;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = 'profile';
    
    /**
     * Maximum count of login attempts per time period.
     *
     * @var integer
     */

    protected $maxLoginAttempts=50;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);
        $user->email_confirm_token = bcrypt($data['email'].time().$data['password']);
        $user->save();

        $emailData = array(
            'email' => $user->email,
            'token' => $user->email_confirm_token
        );

        Mail::send('emails.email-confirmed', $emailData, function($message) use ($user)
        {
            $message->to($user->email, $user->name)->subject('Confirm your e-mail');
        });   

        return $user;
    }
    
    /**
     * Check whether E-mail is confirmed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function isUserEmailConfirmed(Request $request)
    {
        $credentials = $this->getCredentials($request);
        $user = Auth::guard($this->getGuard())->getProvider()->retrieveByCredentials($credentials);

        return $user ? $user->email_confirmed : FALSE;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'), false, true)) {
            
            if($this->isUserEmailConfirmed($request)){
                Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'));
                return $this->handleUserWasAuthenticated($request, $throttles);
            } else {
                $confirmErrorMessage = Lang::has('auth.not_confirmed')
                                           ? Lang::get('auth.not_confirmed', ['username' => $request->input($this->loginUsername())])
                                           : 'You should confirm your e-mail';

                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors([
                        'email_confirmed' => $confirmErrorMessage,
                    ]);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles && ! $lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Registration e-mail confirmation by link
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function confirmEmail(Request $request)
    {
        if ($request->has('email') && $request->has('token'))
        {
            $user = User::where('email', $request->input('email'))
               ->where('email_confirm_token', $request->input('token'))
               ->first();
            
            if($user){
                if( ! $user->email_confirmed){              
                    $user->email_confirmed = true;
                    $user->save();

                    $message = Lang::has('auth.confirmed')
                                   ? Lang::get('auth.confirmed', ['email' => $user->email])
                                   : 'Your e-mail confirmed successfully!';
                } else {
                    $message = Lang::has('auth.confirmed_already')
                                   ? Lang::get('auth.confirmed_already', ['email' => $user->email])
                                   : 'Your e-mail confirmed already!';
                }
                return view('auth.confirm-email', ['message' => $message]);
            }
            
        }
        abort(404, 'Invalid e-mail data');
    }
}
