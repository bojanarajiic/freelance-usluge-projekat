<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * GET /api/reviews
     * Filtri: ?project_id=, ?reviewer_id=, ?reviewee_id=, ?min_rating=, ?max_rating=, ?per_page=
     */
    public function index(Request $request)
    {
        $perPage     = (int) $request->input('per_page', 15);
        $projectId   = $request->input('project_id');
        $reviewerId  = $request->input('reviewer_id');
        $revieweeId  = $request->input('reviewee_id');
        $minRating   = (int) $request->input('min_rating', 1);
        $maxRating   = (int) $request->input('max_rating', 5);

        $query = Review::query()
            ->with([
                'project:id,title,budget,status',
                'reviewer:id,name',
                'reviewee:id,name',
            ])
            ->latest('id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($reviewerId) {
            $query->where('reviewer_id', $reviewerId);
        }
        if ($revieweeId) {
            $query->where('reviewee_id', $revieweeId);
        }

        $query->whereBetween('rating', [$minRating, $maxRating]);

        $reviews = $query->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * GET /api/projects/{project}/reviews
     * Lista recenzija za dati projekat
     */
    public function indexByProject(Project $project, Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $reviews = $project->reviews()
            ->with(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name'])
            ->latest('id')
            ->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * GET /api/users/{user}/received-reviews
     * Recenzije koje je korisnik PRIMIO (kao reviewee)
     */
    public function indexReceivedByUser(User $user, Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $reviews = Review::where('reviewee_id', $user->id)
            ->with(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name'])
            ->latest('id')
            ->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * GET /api/users/{user}/given-reviews
     * Recenzije koje je korisnik DAO (kao reviewer)
     */
    public function indexGivenByUser(User $user, Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $reviews = Review::where('reviewer_id', $user->id)
            ->with(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name'])
            ->latest('id')
            ->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * POST /api/reviews
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'  => ['required', 'exists:projects,id'],
            'reviewer_id' => ['required', 'exists:users,id', 'different:reviewee_id'],
            'reviewee_id' => ['required', 'exists:users,id'],
            'rating'      => ['required', 'integer', 'min:1', 'max:5'],
            'comment'     => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::create($data)
            ->load(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name']);

        return ReviewResource::make($review)
            ->additional(['message' => 'Review created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/reviews/{review}
     */
    public function show(Review $review)
    {
        $review->load(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name']);

        return ReviewResource::make($review);
    }

    /**
     * PUT/PATCH /api/reviews/{review}
     */
    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'project_id'  => ['sometimes', 'required', 'exists:projects,id'],
            'reviewer_id' => ['sometimes', 'required', 'exists:users,id', 'different:reviewee_id'],
            'reviewee_id' => ['sometimes', 'required', 'exists:users,id'],
            'rating'      => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'comment'     => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update($data);
        $review->load(['project:id,title,budget,status', 'reviewer:id,name', 'reviewee:id,name']);

        return ReviewResource::make($review)
            ->additional(['message' => 'Review updated']);
    }

    /**
     * DELETE /api/reviews/{review}
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }

    /**
     * GET /api/projects/{project}/reviews/stats
     * Statistika: prosek, broj, raspodela po ocenama 1..5
     */
    public function statsForProject(Project $project)
    {
        $base = $project->reviews()->selectRaw('count(*) as total, avg(rating) as avg_rating')->first();

        $byRating = $project->reviews()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->all();

        // normalizuj 1..5
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = (int) ($byRating[$i] ?? 0);
        }

        return response()->json([
            'project_id'   => $project->id,
            'total'        => (int) ($base->total ?? 0),
            'avg_rating'   => $base->avg_rating ? round((float) $base->avg_rating, 2) : null,
            'distribution' => $distribution,
        ]);
    }
}
