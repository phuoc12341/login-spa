<?php

namespace App\Services;

use App\Repo\PasswordResetTokenRepository;
use App\Repo\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Mail\MailForgotPassword;
use Mail;
use Password;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Hash;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Auth\Events\Lockout;
use Cache;

class AuthService extends BaseService
{
    protected $passwordResetTokenRepository;
    protected $userRepository;
    protected $loginRedirectTo = '/';
    protected $userService;
    protected $app;

    public function __construct(
        PasswordResetTokenRepository $passwordResetTokenRepository,
        UserRepositoryInterface $userRepository,
        UserService $userService,
        Application $app
    ) {
        parent::__construct($passwordResetTokenRepository);

        $this->passwordResetTokenRepository = $passwordResetTokenRepository;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->app = $app;
    }


    public function createPassportRequest(array $data, $scope = '*', string $grantType = 'password')
    {
        $data['scope'] = $scope;
        $data['grant_type'] = $grantType;
        $data['client_id'] = env('API_CLIENT_ID');
        $data['client_secret'] = env('API_CLIENT_SECRET');

        return app(ServerRequestInterface::class)->withParsedBody($data);
    }

    //---------------------ForgotPasswordController-----------------------------------

    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @return string
     */
    public function sendResetLink(string $email)
    {
        $user = $this->userService->getUserByEmail($email);

        if (is_null($user)) {
            return Password::INVALID_USER;
        }

        if (method_exists($this, 'recentlyCreatedToken') && $this->recentlyCreatedToken($user)) {
            // return Password::RESET_THROTTLED;
            return 'passwords.throttled';
        }

        $this->sendPasswordResetNotification($user);

        return Password::RESET_LINK_SENT;
    }

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param  User  $user
     * @return bool
     */
    public function recentlyCreatedToken(User $user)
    {
        $tokenRecord = $this->passwordResetTokenRepository->findTokenByEmail($user->email);

        return $tokenRecord && $this->tokenRecentlyCreated($tokenRecord->created_at);
    }

    /**
     * Determine if the token was recently created.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenRecentlyCreated($createdAt)
    {
        $throttleOfPasswordResetToken = $this->getConfigAuthPassword('users.throttle') ?? 60;
        if ($throttleOfPasswordResetToken <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds($throttleOfPasswordResetToken)->isFuture();
    }

    /**
     * Get the password broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfigAuthPassword($name)
    {
        return $this->app['config']["auth.passwords.{$name}"];
    }

    public function sendPasswordResetNotification(User $user)
    {
        $this->passwordResetTokenRepository->deleteTokenExistingByEmail($user->email);
        $resetToken = $this->createNewToken();
        $this->createResetPasswordToken($user->email, $resetToken);

        $this->queueMailResetPassword($user, $resetToken, route('password.reset', ['token' => $resetToken]));
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->getKeyForHashToken());
    }

    /**
     * Get a key from APP_Key for hash token 
     *
     * @param  void
     * @return void
     */
    protected function getKeyForHashToken()
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key;
    }

    public function createResetPasswordToken($email, $token)
    {
        return $this->passwordResetTokenRepository->store([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);
    }

    public function queueMailResetPassword($user, $token, $url = null)
    {
        return Mail::queue(new MailForgotPassword($user, $token, $url));
    }

    //---------------------LoginController-----------------------------------

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function hasTooManyLoginAttempts(Request $request)
    {
        return $this->tooManyAttempts(
            $this->throttleKey($request), $this->maxAttempts()
        );
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return bool
     */
    public function tooManyAttempts($key, $maxAttempts)
    {
        if ($this->getAttemptsNumber($key) >= $maxAttempts) {
            if (Cache::has($key.':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttemptsNumber($key)
    {
        return Cache::get($key, 0);
    }

    /**
     * Reset the number of attempts for the given key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function resetAttempts($key)
    {
        return Cache::forget($key);
    }

    /**
     * Get the maximum number of attempts to allow.
     *
     * @return int
     */
    public function maxAttempts()
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function fireLockoutEvent(Request $request)
    {
        event(new Lockout($request));
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [__('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     *
     * @param  string  $key
     * @return int
     */
    public function availableIn($key)
    {
        return Cache::get($key.':timer') - $this->currentTime();
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }

    /**
     *      Login Feature
     */
    public function attempt(Request $request)
    {
        return auth()->guard()->attempt($request->only($this->username(), 'password'), $request->filled('remember'));
    }

    public function username()
    {
        return 'email';
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    function clearLoginAttempts(Request $request)
    {
        $this->clear($this->throttleKey($request));
    }

    /**
     * Clear the hits and lockout timer for the given key.
     *
     * @param  string  $key
     * @return void
     */
    public function clear($key)
    {
        Cache::forget($key);
        Cache::forget($key.':timer');
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function throttleKey(Request $request)
    {
        return Str::lower($request->input($this->username())).'|'.$request->ip();
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function incrementLoginAttempts(Request $request)
    {
        $this->hit($this->throttleKey($request), $this->decayMinutes() * 60);
    }

    /**
     * Increment the counter for a given key for a given decay time.
     *
     * @param  string  $key
     * @param  int  $decaySeconds
     * @return int
     */
    public function hit($key, $decaySeconds = 60)
    {
        Cache::add($key.':timer', $this->availableAt($decaySeconds), $decaySeconds);
        $added = Cache::add($key, 0, $decaySeconds);
        $hits = (int) Cache::increment($key);
        if (! $added && $hits == 1) {
            Cache::put($key, 1, $decaySeconds);
        }

        return $hits;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    public function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? $delay->getTimestamp()
                            : Carbon::now()->addRealSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return \DateTimeInterface|int
     */
    public function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    public function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;
    }

    //---------------------ResetPasswordController-----------------------------------

    public function reset($params)
    {
        $user = $params['userInstance'];

        $this->resetPassword($user, $params['password']);
        event(new PasswordReset($user));
        auth()->guard()->login($user);

        $this->passwordResetTokenRepository->deleteByToken($params['token']);

        return Password::PASSWORD_RESET;
    }

    public function resetPassword($user, $password)
    {
        $params = [
            'email' => $user->email,
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ];

        return $this->userRepository->update($user->id, $params);;
    }

    //---------------------VerificationController-----------------------------------

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(string $userIdFromRequest)
    {
        return $this->userRepository->update((int) $userIdFromRequest, [
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
