<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * GET /api/projects
     * Filteri: ?search=, ?status=, ?client_id=, ?per_page= (default 15)
     */
    public function index(Request $request)
    {
        $perPage   = (int) $request->input('per_page', 15);
        $status    = $request->input('status');
        $clientId  = $request->input('client_id');
        $search    = $request->input('search');

        $query = Project::query()
            ->with(['client:id,name', 'skills:id,name', 'contract.freelancer:id,name'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $query->paginate($perPage);

        return ProjectResource::collection($projects);
    }

    /**
     * POST /api/projects
     * (opciono) skill_ids[] za attach veština.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'budget'      => ['required', 'numeric', 'min:0'],
            'status'      => ['nullable', Rule::in(['open','in_progress','completed','cancelled'])],
            'client_id'   => ['required', 'exists:users,id'],
            'skill_ids'   => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
        ]);

        $data['status'] = $data['status'] ?? 'open';

        $project = Project::create($data);

        if (!empty($data['skill_ids'])) {
            $project->skills()->sync($data['skill_ids']);
        }

        $project->load(['client:id,name', 'skills:id,name', 'contract.freelancer:id,name']);

        return ProjectResource::make($project)
            ->additional(['message' => 'Project created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/projects/{project}
     */
    public function show(Project $project)
    {
        $project->load(['client:id,name', 'skills:id,name', 'contract.freelancer:id,name']);

        return ProjectResource::make($project);
    }

    /**
     * PUT/PATCH /api/projects/{project}
     * (opciono) skill_ids[] za (re)attach.
     */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'       => ['sometimes','required','string','max:255'],
            'description' => ['nullable','string'],
            'budget'      => ['sometimes','required','numeric','min:0'],
            'status'      => ['sometimes','required', Rule::in(['open','in_progress','completed','cancelled'])],
            'client_id'   => ['sometimes','required','exists:users,id'],
            'skill_ids'   => ['nullable','array'],
            'skill_ids.*' => ['integer','exists:skills,id'],
        ]);

        $project->update($data);

        if (array_key_exists('skill_ids', $data)) {
            $project->skills()->sync($data['skill_ids'] ?? []);
        }

        $project->load(['client:id,name', 'skills:id,name', 'contract.freelancer:id,name']);

        return ProjectResource::make($project)
            ->additional(['message' => 'Project updated']);
    }

    /**
     * DELETE /api/projects/{project}
     * Briše projekat i sve povezane entitete u jednoj transakciji.
     */
    public function destroy(Project $project)
    {
         
        $project->load([
            'reviews:id,project_id',
            'proposals:id,project_id',
            'contract:id,project_id',
            'skills:id'
        ]);

        return DB::transaction(function () use ($project) {
           
            $deletedReviews   = $project->reviews->count();
            $deletedProposals = $project->proposals->count();
            $hadContract      = $project->contract !== null;
            $detachedSkills   = $project->skills->count();
 

           
            if ($deletedReviews > 0) {
                \App\Models\Review::where('project_id', $project->id)->delete();
            }

            if ($deletedProposals > 0) {
                \App\Models\Proposal::where('project_id', $project->id)->delete();
            }

            if ($hadContract) {
                $project->contract()->delete();
            }

            if ($detachedSkills > 0) {
                $project->skills()->detach();
            }

            
            $project->delete();
 
            return response()->json([
                'message' => 'Project and related data deleted in a single transaction',
                'deleted' => [
                    'reviews'          => $deletedReviews,
                    'proposals'        => $deletedProposals,
                    'contract_deleted' => (bool) $hadContract,
                    'skills_detached'  => $detachedSkills,
                ],
            ]);
        });
    }


    /**
     * PATCH /api/projects/{project}/status
     * Body: { "status": "in_progress" | "completed" | "cancelled" | "open" }
     */
    public function setStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open','in_progress','completed','cancelled'])],
        ]);

        $project->update(['status' => $data['status']]);

        return ProjectResource::make($project->fresh()->load(['client:id,name', 'skills:id,name', 'contract.freelancer:id,name']))
            ->additional(['message' => 'Project status updated']);
    }

    /**
     * POST /api/projects/{project}/skills
     * Body: { "skill_ids": [1,2,3] }  -> dodaje (bez brisanja ostalih)
     */
    public function attachSkills(Request $request, Project $project)
    {
        $data = $request->validate([
            'skill_ids'   => ['required','array','min:1'],
            'skill_ids.*' => ['integer','exists:skills,id'],
        ]);

        $project->skills()->syncWithoutDetaching($data['skill_ids']);

        return ProjectResource::make($project->fresh()->load(['skills:id,name']))
            ->additional(['message' => 'Skills attached']);
    }

    /**
     * DELETE /api/projects/{project}/skills/{skill}
     */
    public function detachSkill(Project $project, Skill $skill)
    {
        $project->skills()->detach($skill->id);

        return response()->json(['message' => 'Skill detached']);
    }
}
