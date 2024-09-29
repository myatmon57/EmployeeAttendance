<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Detection\MobileDetect; // <-- Import the correct class

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override the attemptLogin method to check for device type.
     */
    protected function attemptLogin($request)
    {
        // Instantiate MobileDetect to detect device
        $detect = new MobileDetect();  // <-- Correct class name

        // Check if the device is a mobile phone
        if ($detect->isMobile()) {
            // Redirect back with an error if it is a mobile device
            return redirect()->back()->withErrors(['message' => 'Login from mobile devices is not allowed.']);
        }

        // Proceed with the login attempt if it's not a mobile device
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }
}
