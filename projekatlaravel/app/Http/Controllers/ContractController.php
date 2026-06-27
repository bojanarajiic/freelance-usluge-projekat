<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    /**
     * GET /api/contracts
     * Podržava ?status=, ?per_page=
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $status  = $request->input('status');

        $query = Contract::query()
            ->with(['project:id,title,budget', 'freelancer:id,name'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        $contracts = $query->paginate($perPage);

        return ContractResource::collection($contracts);
    }

    /**
     * POST /api/contracts
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'    => ['required', 'exists:projects,id', 'unique:contracts,project_id'],
            'freelancer_id' => ['required', 'exists:users,id'],
            'agreed_amount' => ['required', 'numeric', 'min:0'],
            'status'        => ['nullable', Rule::in(['active', 'completed', 'cancelled'])],
            'start_at'      => ['nullable', 'date'],
            'end_at'        => ['nullable', 'date', 'after_or_equal:start_at'],
        ]);

        // default status ako nije prosleđen
        $data['status'] = $data['status'] ?? 'active';

        $contract = Contract::create($data)->load(['project:id,title,budget', 'freelancer:id,name']);

        return ContractResource::make($contract)
            ->additional(['message' => 'Contract created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/contracts/{contract}
     */
    public function show(Contract $contract)
    {
        $contract->load(['project:id,title,budget', 'freelancer:id,name']);

        return ContractResource::make($contract);
    }

    /**
     * PUT/PATCH /api/contracts/{contract}
     */
    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'project_id'    => [
                'sometimes', 'required', 'exists:projects,id',
                Rule::unique('contracts', 'project_id')->ignore($contract->id),
            ],
            'freelancer_id' => ['sometimes', 'required', 'exists:users,id'],
            'agreed_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status'        => ['sometimes', 'required', Rule::in(['active', 'completed', 'cancelled'])],
            'start_at'      => ['nullable', 'date'],
            'end_at'        => ['nullable', 'date', 'after_or_equal:start_at'],
        ]);

        $contract->update($data);
        $contract->load(['project:id,title,budget', 'freelancer:id,name']);

        return ContractResource::make($contract)
            ->additional(['message' => 'Contract updated']);
    }

    /**
     * DELETE /api/contracts/{contract}
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();

        return response()->json([
            'message' => 'Contract deleted',
        ]);
    }
}
