<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Permission;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use App\Models\Screen;
use App\Helpers\SecureDataHelper;
class AuthController extends Controller{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = trim($request->email);
        $password = $request->password;
        $throttleKey = Str::lower($email) . '|' . $request->ip();

        if (!RateLimiter::attempt($throttleKey, 5, function () {
            return true;
        }, 60)) {
            return response()->json([
                'message' => 'Too many login attempts. Try again later.'
            ], 429);
        }

        $user = User::authenticate($email, $password);
        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $userAccount = User::getUserAccount($user);
        if (!$userAccount) {
            return response()->json([
                'message' => 'User account not found'
            ], 404);
        }

        User::updateAccountData($userAccount);

        $loginData = [
            'id' => Crypt::encryptString($userAccount->id),
            'email' => base64_encode($userAccount->email),
            'username' => $userAccount->user_name,
            'image' => $userAccount->image,
            'title' => $userAccount->title,
            'userType' => $user->use_type == 2 ? 'admin' : 'user',
            'role' => Crypt::encryptString($userAccount->role),
            'brand' => Crypt::encryptString($userAccount->brand),
            'emp_id' => Crypt::encryptString($userAccount->employees_id),
            'master_user' => Crypt::encryptString($userAccount->master_user_id),
            'loggedin' => 1,
        ];

        $token = JWTAuth::fromUser($user);

        RateLimiter::clear($throttleKey);

        return response()->json([
            'user' => $loginData,
            'token' => $token,
        ], 200);
    }

    public function userPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid data provided',
                'messages' => $validator->errors(),
            ], 422);
        }

        $role = $request->input('role');

        $permissionsWithScreens = [];
        $gro = [];

        $user = JWTAuth::parseToken()->authenticate();

        if ($user->use_type == 2) {

            $Permissions = DB::table('screen')
                ->whereIn('screen.use_system', ['AU'])
                ->where('menu', 1)
                ->select('groups', 'name', 'url', 'menu')
                ->get();

            $groupedPermissions = $Permissions->groupBy('groups');

            foreach ($groupedPermissions as $key => $value) {
                foreach ($value as $value2) {
                    $value2->type = "link";
                    unset($value2->groups);
                    unset($value2->menu);
                }

                $permissionsWithScreens[$key] = [
                    'title' => Group::getGroup($key)->name,
                    'type' => "sub",
                    'active' => false,
                    'children' => $value
                ];
            }
        } else {

            $Permissions = Permission::getGroupByRole($role);

            foreach ($Permissions as $permission) {
                $groups = [];
                $screens = Permission::getScreenByGroupAndRole($permission->groups, $role);

                foreach ($screens as $s) {
                    $screen = Screen::getScreen($s->screen);
                    $screen->type = 'link';
                    $groups[] = $screen;
                }

                if (count($groups) > 0) {
                    $group = Group::getGroup($permission->groups);
                    $permissionsWithScreens[$permission->groups] = [
                        'title' => $group->name,
                        'type' => "sub",
                        'active' => false,
                        'children' => $groups
                    ];
                }
            }
        }

        return response()->json([
            "data" => $permissionsWithScreens
        ], 200);
    }

    public function routes(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->use_type == 2) {
            $allowedRoutes = DB::table('screen')
                ->whereIn('screen.use_system', ['AU'])
                ->select(
                    'screen.url',
                    DB::raw('1 as `add`'),
                    DB::raw('1 as `edit`'),
                    DB::raw('1 as `delete`'),
                    DB::raw('1 as `view`'),
                    DB::raw('1 as `assign`')
                )
                ->get();
        } else {
            $validator = Validator::make($request->all(), [
                'role' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid data provided',
                    'messages' => $validator->errors(),
                ], 401);
            };
            try {
                $role = Crypt::decrypt($request->input('role'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json(['error' => 'Failed to decrypt role'], 400);
            }
            $allowedRoutes = DB::table('permission')
                ->join('screen', 'permission.screen', '=', 'screen.id')
                ->where('permission.role', $role)
                ->whereIn('screen.use_system', ['AU'])
                ->select('screen.url', 'permission.add as add', 'permission.edit as edit', 'permission.delete as delete', 'permission.view as view', 'permission.assign as assign')
                ->get();
        }
        $jsonPayload = json_encode($allowedRoutes);
        $compressedPayload = gzcompress($jsonPayload);
        $finalData = base64_encode($compressedPayload);
        return response()->json([
            'data' => $finalData,
        ], 200);
    }

}
