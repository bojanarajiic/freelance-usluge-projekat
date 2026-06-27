<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SkillController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//CRUD
Route::apiResource('skills', SkillController::class);
Route::apiResource('contracts', ContractController::class);
Route::apiResource('projects', ProjectController::class);
Route::apiResource('proposals', ProposalController::class);

// Dodatne rute za Project
Route::patch('projects/{project}/status', [ProjectController::class, 'setStatus']);
Route::post('projects/{project}/skills', [ProjectController::class, 'attachSkills']);
Route::delete('projects/{project}/skills/{skill}', [ProjectController::class, 'detachSkill']);

// Dodatne rute za Proposal
Route::get('projects/{project}/proposals', [ProposalController::class, 'indexByProject']);
Route::post('projects/{project}/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
Route::post('projects/{project}/proposals/{proposal}/reject', [ProposalController::class, 'reject']);