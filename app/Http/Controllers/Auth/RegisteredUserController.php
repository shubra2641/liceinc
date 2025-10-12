<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\ConfigHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Models\User;
use App\Services\Email\Facades\Email;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controller for handling user registration requests with enhanced security.
 *
 * This controller manages user registration with comprehensive validation,
 * anti-spam protection, and email notifications. It includes support for
 * Google reCAPTCHA and human verification questions.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Anti-spam protection with reCAPTCHA and human questions
 * - Email verification and notification system
 *
 * @version 1.0.6
 */
class RegisteredUserController extends Controller
{
    protected Email $emailService;
    /**
     * Create a new controller instance.
     *
     * @param  Email  $emailService  The email service for sending notifications
     */
    public function __construct(Email $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Display the registration view.
     *
     * Shows the user registration form with all necessary fields
     * and anti-spam protection options.
     *
     * @return View The registration view
     */
    public function create(): View
    {
        $registrationSettings = $this->getRegistrationSettings();
        return view('auth.register', ['registrationSettings' => $registrationSettings]);
    }
    /**
     * Handle an incoming registration request with enhanced security.
     *
     * Validates user input, performs anti-spam checks, creates the user,
     * sends welcome and notification emails, and logs the user in.
     *
     * @param  RegisterRequest  $request  The registration request
     *
     * @return RedirectResponse Redirect to dashboard on success or back with errors
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception When database operations fail
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $this->validateRegistrationRequest($request);
            $this->validateAntiSpamProtection($request);
            $user = $this->createUser($request);
            $this->handleUserRegistration($user);
            Auth::login($user);
            DB::commit();
            return redirect(route('dashboard', absolute: false));
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Registration failed. Please try again.']);
        }
    }
    /**
     * Validate the basic registration request with enhanced security.
     *
     * @param  RegisterRequest  $request  The current request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateRegistrationRequest(RegisterRequest $request): void
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'lastname' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phonenumber' => ['nullable', 'string', 'max:50', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'country' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
        ], [
            'firstname.regex' => 'First name can only contain letters and spaces.',
            'lastname.regex' => 'Last name can only contain letters and spaces.',
            'phonenumber.regex' => 'Phone number contains invalid characters.',
            'country.regex' => 'Country name can only contain letters and spaces.',
        ]);
    }
    /**
     * Validate anti-spam protection mechanisms.
     *
     * @param  RegisterRequest  $request  The current request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateAntiSpamProtection(RegisterRequest $request): void
    {
        $this->validateCaptcha($request);
        $this->validateHumanQuestion($request);
    }
    /**
     * Validate Google reCAPTCHA if enabled.
     *
     * @param  RegisterRequest  $request  The current request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateCaptcha(RegisterRequest $request): void
    {
        $enableCaptcha = ConfigHelper::getSetting('enable_captcha', false);
        $captchaSecret = ConfigHelper::getSetting('captcha_secret_key', '');
        if (! $enableCaptcha || ! $captchaSecret) {
            return;
        }
        $token = $request->validated('g-recaptcha-response');
        if (empty($token)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json(['errors' => ['g-recaptcha-response' => [__('Please complete the captcha')]]], 422)
            );
        }
        if (
            ! $this->verifyRecaptcha(
                is_string($token) ? $token : '',
                is_string($captchaSecret) ? $captchaSecret : '',
                $request->ip()
            )
        ) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json(['errors' => ['g-recaptcha-response' => [__('Captcha verification failed')]]], 422)
            );
        }
    }
    /**
     * Validate human verification question if enabled.
     *
     * @param  RegisterRequest  $request  The current request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateHumanQuestion(RegisterRequest $request): void
    {
        $enableHumanQuestion = ConfigHelper::getSetting('enable_human_question', true);
        if (! $enableHumanQuestion) {
            return;
        }
        $humanQuestions = $this->getHumanQuestions();
        $given = strtolower(trim(
            is_string($request->validated('human_answer', ''))
                ? $request->validated('human_answer', '')
                : ''
        ));
        $index = $request->validated('human_question_index', null);
        if (
            ! $this->isValidHumanAnswer(
                $given,
                is_numeric($index) ? (int)$index : 0,
                $humanQuestions
            )
        ) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json([
                    'errors' => [
                        'human_answer' => [__('Incorrect answer to the anti-spam question')]
                    ]
                ], 422)
            );
        }
    }
    /**
     * Get human questions from configuration.
     *
     * @return array The human questions array
     */
    /**
     * @return array<string, mixed>
     */
    private function getHumanQuestions(): array
    {
        $humanQuestionsJson = ConfigHelper::getSetting('human_questions', null);
        if (empty($humanQuestionsJson)) {
            return [];
        }
        $decoded = json_decode(is_string($humanQuestionsJson) ? $humanQuestionsJson : '', true);
        $result = is_array($decoded) ? $decoded : [];

        /**
 * @var array<string, mixed> $typedResult
*/
        $typedResult = $result;
        return $typedResult;
    }
    /**
     * Check if the human answer is valid.
     *
     * @param  string  $given  The given answer
     * @param  mixed  $index  The question index
     * @param  array  $humanQuestions  The human questions array
     *
     * @return bool True if answer is valid, false otherwise
     */
    /**
     * @param array<string, mixed> $humanQuestions
     */
    private function isValidHumanAnswer(string $given, int $index, array $humanQuestions): bool
    {
        if ($given === '') {
            return false;
        }
        if (! isset($humanQuestions[(string)$index])) {
            $expected = ConfigHelper::getSetting('human_question_answer', '5');
            return strtolower(trim(
                is_string($expected) ? $expected : ''
            )) === $given;
        }

        $questionData = $humanQuestions[(string)$index] ?? null;
        $answer = is_array($questionData) ? ($questionData['answer'] ?? null) : null;
        $expected = strtolower(trim(is_string($answer) ? $answer : ''));
        return $expected === $given;
    }
    /**
     * Create a new user from the request data with enhanced security.
     *
     * @param  RegisterRequest  $request  The current request
     *
     * @return User The created user
     */
    private function createUser(RegisterRequest $request): User
    {
        return User::create([
            'name' => (is_string($this->sanitizeInput($request->firstname))
                ? $this->sanitizeInput($request->firstname)
                : '') . ' ' . (is_string($this->sanitizeInput($request->lastname))
                ? $this->sanitizeInput($request->lastname)
                : ''),
            'firstname' => $this->sanitizeInput($request->firstname),
            'lastname' => $this->sanitizeInput($request->lastname),
            'email' => $this->sanitizeInput($request->email),
            'password' => Hash::make(is_string($request->password) ? $request->password : ''),
            'phonenumber' => $this->sanitizeInput($request->phonenumber),
            'country' => $this->sanitizeInput($request->country),
        ]);
    }
    /**
     * Handle post-registration tasks.
     *
     * @param  User  $user  The registered user
     */
    private function handleUserRegistration(User $user): void
    {
        event(new Registered($user));
        $this->sendWelcomeEmail($user);
        $this->sendAdminNotification($user);
    }
    /**
     * Send welcome email to the new user.
     *
     * @param  User  $user  The registered user
     */
    private function sendWelcomeEmail(User $user): void
    {
        try {
            $this->emailService->sendWelcome($user, [
                'registration_date' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // Silently handle email errors to not fail registration
            Log::warning('Welcome email failed to send', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Send admin notification about new user registration.
     *
     * @param  User  $user  The registered user
     */
    private function sendAdminNotification(User $user): void
    {
        try {
            $this->emailService->sendNewUserNotification($user);
        } catch (\Exception $e) {
            // Silently handle email errors to not fail registration
            Log::warning('Admin notification email failed to send', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Verify Google reCAPTCHA token with Google's API.
     *
     * @param  string  $token  The reCAPTCHA token
     * @param  string  $secret  The reCAPTCHA secret key
     * @param  string|null  $remoteIp  The remote IP address
     *
     * @return bool True if verification successful, false otherwise
     */
    private function verifyRecaptcha(string $token, string $secret, ?string $remoteIp = null): bool
    {
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);
            if (! $response->ok()) {
                return false;
            }
            $body = $response->json();
            $success = is_array($body) ? ($body['success'] ?? null) : null;
            return is_array($body) && isset($body['success']) && $success === true;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Get registration settings for the view.
     *
     * @return array The registration settings
     */
    /**
     * @return array<string, mixed>
     */
    private function getRegistrationSettings(): array
    {
        $enableCaptcha = ConfigHelper::getSetting('enable_captcha', false);
        $captchaSiteKey = ConfigHelper::getSetting('captcha_site_key', '');
        $enableHumanQuestion = ConfigHelper::getSetting('enable_human_question', true);
        $humanQuestionsJson = ConfigHelper::getSetting('human_questions', null);
        $humanQuestions = [];
        if (! empty($humanQuestionsJson)) {
            $decoded = json_decode(
                is_string($humanQuestionsJson) ? $humanQuestionsJson : '',
                true
            );
            $humanQuestions = is_array($decoded) ? $decoded : [];
        }
        // Choose a random question (server-side) and include its index in a hidden field
        $selectedQuestionIndex = null;
        $selectedQuestionText = null;
        if (! empty($humanQuestions)) {
            $selectedQuestionIndex = array_rand($humanQuestions);
            $selectedQuestion = $humanQuestions[$selectedQuestionIndex] ?? null;
            $selectedQuestionText = (is_array($selectedQuestion) && isset($selectedQuestion['question']))
                ? $selectedQuestion['question']
                : null;
        }
        return [
            'enableCaptcha' => $enableCaptcha,
            'captchaSiteKey' => $captchaSiteKey,
            'enableHumanQuestion' => $enableHumanQuestion,
            'selectedQuestionIndex' => $selectedQuestionIndex,
            'selectedQuestionText' => $selectedQuestionText,
        ];
    }
}
