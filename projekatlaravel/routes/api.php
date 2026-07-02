<?php

use App\Http\Controllers\{
    AuthController,
    SkillController,
    ContractController,
    IntegrationController,
    ProjectController,
    ProposalController,
    ReportController,
    ReviewController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC (bez autentikacije)
|--------------------------------------------------------------------------
*/

// Auth
Route::post('register', [AuthController::class, 'register']); // ili prebaci pod admin ako želiš zatvoren sistem
Route::post('login',    [AuthController::class, 'login']);

// Javno listanje projekata (bar jedna apiResource ruta)
Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);

// Javno: pregled veština (CRUD ostaje za admina)
Route::apiResource('skills', SkillController::class)->only(['index', 'show']);

// Javno: statistika i pregled recenzija za projekat
Route::get('projects/{project}/reviews',        [ReviewController::class, 'indexByProject']);
Route::get('projects/{project}/reviews/stats',  [ReviewController::class, 'statsForProject']);


/*
|--------------------------------------------------------------------------
| AUTH (potrebna prijava)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Self-korisnik
    Route::get('me',       [AuthController::class, 'me']);
    Route::post('logout',  [AuthController::class, 'logout']);

    // Uobičajene zaštićene rute dostupne svim ulogama (autorizacija fino ide kroz Policy)
    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::apiResource('reviews',   ReviewController::class)->only(['store', 'show', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN (sistemska administracija)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        // Puni CRUD nad veštinama
        Route::apiResource('skills', SkillController::class)->except(['index', 'show']);

        // (opciono) admin može sve nad ugovorima/projektima/proposalima:
        // Route::apiResource('contracts', ContractController::class);
        // Route::apiResource('projects',  ProjectController::class)->except(['index','show']);
        // Route::apiResource('proposals', ProposalController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | CLIENT (vlasnik projekata)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:client')->group(function () {
        // Kreiranje/izmena/brisanje sopstvenih projekata
        Route::apiResource('projects', ProjectController::class)->only(['store', 'update', 'destroy']);

        // Menjanje statusa projekta
        Route::patch('projects/{project}/status', [ProjectController::class, 'setStatus']);

        // Upravljanje veštinama traženim na projektu
        Route::post('projects/{project}/skills',        [ProjectController::class, 'attachSkills']);
        Route::delete('projects/{project}/skills/{skill}', [ProjectController::class, 'detachSkill']);


        // Rad sa ponudama na svom projektu (prihvatanje/odbijanje)
        Route::get('projects/{project}/proposals',                   [ProposalController::class, 'indexByProject']);
        Route::post('projects/{project}/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
        Route::post('projects/{project}/proposals/{proposal}/reject', [ProposalController::class, 'reject']);

        // (opciono) pregled/izmene ugovora proisteklih iz njegovih projekata
        Route::apiResource('contracts', ContractController::class)->only(['update', 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | FREELANCER (ponude i rad na ugovorima)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:freelancer')->group(function () {
        // Freelancer šalje/menja/otkazuje svoje ponude
        Route::apiResource('proposals', ProposalController::class)->only(['store', 'update', 'destroy']);

        // (opciono) pregled sopstvenih ponuda kroz /proposals?freelancer_id=me — rešava Policy
        // (opciono) pregled svojih ugovora/recenzija preko /contracts, /reviews — ograničeno Policy-jem
    });

    /*
    |--------------------------------------------------------------------------
    | ZA SVE ULOGE 
    |--------------------------------------------------------------------------
    */

    Route::apiResource('proposals', ProposalController::class)->only(['index', 'show']);
    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::get('users/{user}/received-reviews', [ReviewController::class, 'indexReceivedByUser']);
    Route::get('users/{user}/given-reviews',    [ReviewController::class, 'indexGivenByUser']);
});



// REST servis #1: FX konverzija
Route::get('integrations/fx/convert', [IntegrationController::class, 'fxConvert']);

// REST servis #2: Avatar (dovoljna je ova JSON varijanta)
Route::get('integrations/avatar/url', [IntegrationController::class, 'avatarUrl']);

// OBRAĐENI podaci (FX + contracts iz baze)
Route::get('reports/contracts/fx-summary', [ReportController::class, 'contractsFxSummary']);
