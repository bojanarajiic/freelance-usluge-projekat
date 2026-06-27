<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    /**
     * GET /api/skills
     * Podržava ?search=, ?per_page=
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $search  = $request->input('search');

        $query = Skill::query()->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $skills = $query->paginate($perPage);

        return SkillResource::collection($skills);
    }

    /**
     * POST /api/skills
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name'],
        ]);

        $skill = Skill::create($data);

        return SkillResource::make($skill)
            ->additional(['message' => 'Skill created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/skills/{skill}
     */
    public function show(Skill $skill)
    {
        return SkillResource::make($skill);
    }

    /**
     * PUT/PATCH /api/skills/{skill}
     */
    public function update(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('skills', 'name')->ignore($skill->id),
            ],
        ]);

        $skill->update($data);

        return SkillResource::make($skill)
            ->additional(['message' => 'Skill updated']);
    }

    /**
     * DELETE /api/skills/{skill}
     */
    public function destroy(Skill $skill)
    {
        $skill->delete();

        return response()->json([
            'message' => 'Skill deleted',
        ]);
    }
}
