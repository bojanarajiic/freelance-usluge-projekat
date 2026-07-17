<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'client_id', 'title', 'description',
        'budget_min', 'budget_max', 'status', 'deadline_at'
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'budget_min'  => 'decimal:2',
        'budget_max'  => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)->withTimestamps();
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    // Jedan prihvaćeni bid vodi u ugovor
    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
       public function reviews()
    {
        return $this->hasMany(Review::class);
    }

   
}
