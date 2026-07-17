<?php
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'email'       => ['required','email','max:255','unique:users,email'],
            'password'    => ['required','string','min:8'],
            'role'        => ['required', Rule::in(['admin','client','freelancer'])],
            'affiliation' => ['nullable','string','max:255'],
            'orcid'       => ['nullable','string','max:50'],
        ]);

        $user = User::create(array_merge($data, [
            'password' => Hash::make($data['password']),
        ]));

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user->load(['profile','skills'])),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user->load(['profile','skills'])),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load(['profile','skills']));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * PUT /api/me
     * Admin može menjati i "role"; ostali korisnici ne.
     */
    public function updateUser(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name'        => ['sometimes','required','string','max:255'],
            'email'       => ['sometimes','required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'affiliation' => ['nullable','string','max:255'],
            'orcid'       => ['nullable','string','max:50'],
        ];

        // role samo admin
        if ($user->isAdmin()) {
            $rules['role'] = ['sometimes','required', Rule::in(['admin','client','freelancer'])];
        }

        $data = $request->validate($rules);
        $user->update($data);

        return new UserResource($user->fresh()->load(['profile','skills']));
    }

    /**
     * PUT /api/me/password
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required','string'],
            'password'         => ['required','string','min:8','confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated']);
    }

    /**
     * POST /api/me/profile (create or update / upsert)
     */
    public function upsertProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'bio'           => ['nullable','string'],
            'github_url'    => ['nullable','url','max:255'],
            'portfolio_url' => ['nullable','url','max:255'],
            'hourly_rate'   => ['nullable','numeric','min:0'],
            'location'      => ['nullable','string','max:255'],
        ]);

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json([
            'message' => 'Profile saved',
            'profile' => new ProfileResource($profile),
        ]);
    }

    /**
     * POST /api/me/skills   body: { skills: [{id:1, level:"expert"}, ...] }
     * Dodaje/menja nivoe; ne briše ostale ako nisu u listi
     */
    public function attachSkillsToMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'skills'           => ['required','array','min:1'],
            'skills.*.id'      => ['required','integer','exists:skills,id'],
            'skills.*.level'   => ['nullable','string','max:20'],  
        ]);

        $attach = [];
        foreach ($data['skills'] as $s) {
            $attach[$s['id']] = ['level' => $s['level'] ?? null];
        }

        $user->skills()->syncWithoutDetaching($attach);

        return new UserResource($user->fresh()->load(['profile','skills']));
    }

    /**
     * DELETE /api/me/skills/{skill}
     */
    public function detachSkillFromMe(Request $request, Skill $skill)
    {
        $user = $request->user();
        $user->skills()->detach($skill->id);

        return response()->json(['message' => 'Skill detached']);
    }

    /**
     * DELETE /api/me  (opciono)
     */
    public function destroyMe(Request $request)
    {
        $user = $request->user();
       
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted']);
    }
}
