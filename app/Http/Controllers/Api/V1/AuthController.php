<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ساخت حساب کاربری و صدور توکن — همان قواعد فرم ثبت‌نام وب
     */
    public function register(ApiRegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => trim($request->validated('name')),
            'email' => strtolower(trim($request->validated('email'))),
            'password' => $request->validated('password'),
            'is_admin' => false,
        ]);

        return response()->json([
            'message' => 'حساب کاربری شما ساخته شد؛ خوش آمدید!',
            'data' => new UserResource($user),
            'token' => $user->createToken($this->deviceName($request))->plainTextToken,
        ], 201);
    }

    /**
     * ورود و صدور توکن — پیام خطا عمداً نمی‌گوید ایمیل بود یا رمز
     */
    public function login(ApiLoginRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower(trim($request->validated('email'))))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['اطلاعات ورود نادرست است.'],
            ]);
        }

        return response()->json([
            'message' => 'خوش آمدید!',
            'data' => new UserResource($user),
            'token' => $user->createToken($this->deviceName($request))->plainTextToken,
        ]);
    }

    /**
     * پروفایل کاربرِ صاحب توکن
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * خروج — فقط همان توکنی که درخواست را زده باطل می‌شود؛
     * توکن‌های دستگاه‌های دیگر کاربر دست‌نخورده می‌مانند.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'توکن شما باطل شد؛ تا دیدار بعدی!',
        ]);
    }

    /**
     * نام دستگاه برای تشخیص توکن‌ها در پنل — مثل «iPhone 15» یا «api»
     */
    private function deviceName(Request $request): string
    {
        $name = trim((string) $request->input('device_name'));

        return $name === '' ? 'api' : mb_substr($name, 0, 100);
    }
}
