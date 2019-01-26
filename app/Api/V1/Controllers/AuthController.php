<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Validator;
use Config;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Exceptions\JWTException;
use Dingo\Api\Exception\ValidationHttpException;

class AuthController extends Controller
{
    use Helpers;

    /**
     * @SWG\Post(path="/api/auth/login",
     *     tags={"Authentification"},
     *     description="Login action",
     *     operationId="login",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Response(response=401, description="unauthorized"),
     *     @SWG\Response(response=500, description="server error")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $validator = Validator::make($credentials, [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()) {
            throw new ValidationHttpException($validator->errors()->all());
        }

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->response->errorUnauthorized();
            }
        } catch (JWTException $e) {
            return $this->response->error('could_not_create_token', 500);
        }

        return response()->json(compact('token'));
    }

    /**
     * @SWG\Post(path="/api/auth/signup",
     *     tags={"Authentification"},
     *     description="Signup action",
     *     operationId="signup",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Response(response=401, description="unauthorized"),
     *     @SWG\Response(response=500, description="server error")
     * )
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|void
     */
    public function signup(Request $request)
    {
        $signupFields = Config::get('boilerplate.signup_fields');
        $hasToReleaseToken = Config::get('boilerplate.signup_token_release');

        $userData = $request->only($signupFields);

        $validator = Validator::make($userData, Config::get('boilerplate.signup_fields_rules'));

        if($validator->fails()) {
            throw new ValidationHttpException($validator->errors()->all());
        }

        User::unguard();
        $user = User::create($userData);
        User::reguard();

        if(!$user->id) {
            return $this->response->error('could_not_create_user', 500);
        }

        if($hasToReleaseToken) {
            return $this->login($request);
        }
        
        return $this->response->created();
    }

    /**
     * @SWG\Post(path="/api/auth/recovery",
     *     tags={"Authentification"},
     *     description="Recovery action",
     *     operationId="recovery",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Response(response=401, description="unauthorized"),
     *     @SWG\Response(response=500, description="server error")
     * )
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     */
    public function recovery(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => 'required'
        ]);

        if($validator->fails()) {
            throw new ValidationHttpException($validator->errors()->all());
        }

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject(Config::get('boilerplate.recovery_email_subject'));
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return $this->response->noContent();
            case Password::INVALID_USER:
                return $this->response->errorNotFound();
        }
    }

    /**
     * @SWG\Post(path="/api/auth/reset",
     *     tags={"Authentification"},
     *     description="Reset password action",
     *     operationId="reset",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password_confirmation",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="token",
     *                  type="string"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Response(response=401, description="unauthorized"),
     *     @SWG\Response(response=500, description="server error")
     * )
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|void
     */
    public function reset(Request $request)
    {
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $validator = Validator::make($credentials, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        if($validator->fails()) {
            throw new ValidationHttpException($validator->errors()->all());
        }
        
        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                if(Config::get('boilerplate.reset_token_release')) {
                    return $this->login($request);
                }
                return $this->response->noContent();

            default:
                return $this->response->error('could_not_reset_password', 500);
        }
    }
}