<?php 
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';
    public const ROLE_FREELANCER = 'freelancer';

    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];


     // Helpers za role
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isClient(): bool { return $this->role === 'client'; }
    public function isFreelancer(): bool { return $this->role === 'freelancer'; }


    // ---- Relacije
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot('level') // 1–5
            ->withTimestamps();
    }

    // Projekti koje je objavio kao klijent
    public function projectsPosted()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    // Ponude koje je poslao kao freelancer
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'freelancer_id');
    }

    // Ugovori u kojima je freelancer
    public function contractsAsFreelancer()
    {
        return $this->hasMany(Contract::class, 'freelancer_id');
    }

    // Recenzije
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }



      // Automatski dodaj polja u JSON odgovore
    protected $appends = ['avatar_url', 'avatar_fallback_url'];

    public function getAvatarUrlAttribute(): string
    {
        $email = strtolower(trim($this->email ?? ''));
        $hash  = md5($email);
        // Gravatar (javni servis, bez ključa)
        return "https://www.gravatar.com/avatar/{$hash}?s=128&d=identicon";
    }

    public function getAvatarFallbackUrlAttribute(): string
    {
        // DiceBear kao besplatan fallback (seed = ime pre @, ili id)
        $seed = $this->name ?: ($this->id ?? 'user');
        return "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($seed);
    }
}
