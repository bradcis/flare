<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Flare\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Flare\Events\CreateCharacterEvent;
use App\Flare\Models\GameRace;
use App\Flare\Models\GameClass;
use App\Flare\Models\GameMap;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['required', 'string', 'min:3', 'unique:characters'],
            'race' => ['required'],
            'class' => ['required'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Flare\Models\User
     */
    protected function create(array $data, string $ip)
    {

        $user = User::where('ip_address', $ip)->where('is_banned', true)->first();

        if ($user) {
            $until = !is_null($user->unbanned_at) ? $user->unbanned_at->format('l jS \\of F Y h:i:s A') . ' ' . $user->unbanned_at->timezoneName . '.' : 'For ever.';

            throw new \Exception('You has been banned until: ' . $until);
        }

        if (User::where('ip_address', $ip)->count() >= 10) {
            throw new \Exception('You cannot register anymore characters.');
        }
        
        $token = Str::random(80);

        return User::create([
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'game_key'         => hash('sha256', $token),
            'private_game_key' => $token,
            'ip_address'       => $ip,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $map = GameMap::where('default', true)->first();

        if (is_null($map)) {
            return redirect()->back()->with('error', 'No game map has been set as default or created. Registration is disabled.');
        }

        $this->validator($request->all())->validate();

        try {
            $user = $this->create($request->all(), $request->ip());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getmessage());
        }

        event(new Registered($user));

        event(new CreateCharacterEvent($user, $map, $request));

        $this->guard()->login($user);

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {

        return view('auth.register', [
            'races' => GameRace::pluck('name', 'id'),
            'classes' => GameClass::pluck('name', 'id'),
        ]);
    }
}
