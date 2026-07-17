<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProposalController extends Controller
{
    /**
     * GET /api/proposals
     * Filteri: ?status=, ?project_id=, ?freelancer_id=, ?per_page=
     */
    public function index(Request $request)
    {
        $perPage       = (int) $request->input('per_page', 15);
        $status        = $request->input('status');
        $projectId     = $request->input('project_id');
        $freelancerId  = $request->input('freelancer_id');

        $query = Proposal::query()
            ->with(['project:id,title,status', 'freelancer:id,name'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($freelancerId) {
            $query->where('freelancer_id', $freelancerId);
        }

        $proposals = $query->paginate($perPage);

        return ProposalResource::collection($proposals);
    }

    /**
     * GET /api/projects/{project}/proposals
     * Proposals samo za jedan projekat
     */
    public function indexByProject(Project $project, Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $proposals = $project->proposals()
            ->with(['project:id,title,status', 'freelancer:id,name'])
            ->latest('id')
            ->paginate($perPage);

        return ProposalResource::collection($proposals);
    }

    /**
     * POST /api/proposals
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'    => ['required','exists:projects,id'],
            'freelancer_id' => ['required','exists:users,id'],
            'amount'        => ['required','numeric','min:0'],
            'delivery_days' => ['required','integer','min:1'],
            'cover_letter'  => ['nullable','string'],
            'status'        => ['nullable', Rule::in(['pending','accepted','rejected'])],
        ]);

        $data['status'] = $data['status'] ?? 'pending';

        $proposal = Proposal::create($data)
            ->load(['project:id,title,status', 'freelancer:id,name']);

        return ProposalResource::make($proposal)
            ->additional(['message' => 'Proposal created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/proposals/{proposal}
     */
    public function show(Proposal $proposal)
    {
        $proposal->load(['project:id,title,status', 'freelancer:id,name']);

        return ProposalResource::make($proposal);
    }

    /**
     * PUT/PATCH /api/proposals/{proposal}
     */
    public function update(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'project_id'    => ['sometimes','required','exists:projects,id'],
            'freelancer_id' => ['sometimes','required','exists:users,id'],
            'amount'        => ['sometimes','required','numeric','min:0'],
            'delivery_days' => ['sometimes','required','integer','min:1'],
            'cover_letter'  => ['nullable','string'],
            'status'        => ['sometimes','required', Rule::in(['pending','accepted','rejected'])],
        ]);

        $proposal->update($data);
        $proposal->load(['project:id,title,status', 'freelancer:id,name']);

        return ProposalResource::make($proposal)
            ->additional(['message' => 'Proposal updated']);
    }

    /**
     * DELETE /api/proposals/{proposal}
     */
    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return response()->json(['message' => 'Proposal deleted']);
    }

    /**
     * POST /api/projects/{project}/proposals/{proposal}/accept
     * Kreira contract (ako ne postoji) i setuje status.
     */
    public function accept(Project $project, Proposal $proposal)
    {
        // validacija da proposal pripada projektu
        if ($proposal->project_id !== $project->id) {
            return response()->json(['message' => 'Proposal does not belong to this project'], 422);
        }

        return DB::transaction(function () use ($project, $proposal) {
            // ako već postoji ugovor za ovaj projekat, ne može novi (unique project_id)
            $existing = Contract::where('project_id', $project->id)->first();
            if ($existing) {
                // samo update status proposal-a (drugi rejected ako želiš)
                $proposal->update(['status' => 'accepted']);
                return ProposalResource::make($proposal->fresh()->load(['project:id,title,status', 'freelancer:id,name']))
                    ->additional(['message' => 'Proposal accepted (contract already exists for project)']);
            }

            // setuj accepted i kreiraj ugovor
            $proposal->update(['status' => 'accepted']);

            $contract = Contract::create([
                'project_id'    => $project->id,
                'freelancer_id' => $proposal->freelancer_id,
                'agreed_amount' => $proposal->amount,
                'status'        => 'active',
                'start_at'      => now(),
            ]);

            return response()->json([
                'message'  => 'Proposal accepted and contract created',
                'proposal' => new ProposalResource($proposal->fresh()->load(['project:id,title,status', 'freelancer:id,name'])),
                'contract' => new \App\Http\Resources\ContractResource(
                    $contract->load(['project:id,title', 'freelancer:id,name'])
                ),
            ]);
        });
    }

    /**
     * POST /api/projects/{project}/proposals/{proposal}/reject
     */
    public function reject(Project $project, Proposal $proposal)
    {
        if ($proposal->project_id !== $project->id) {
            return response()->json(['message' => 'Proposal does not belong to this project'], 422);
        }

        $proposal->update(['status' => 'rejected']);

        return ProposalResource::make($proposal->fresh()->load(['project:id,title,status', 'freelancer:id,name']))
            ->additional(['message' => 'Proposal rejected']);
    }
}
