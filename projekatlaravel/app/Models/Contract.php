<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'project_id', 'freelancer_id', 'agreed_amount',
        'start_at', 'end_at', 'status'
    ];

    protected $casts = [
        'agreed_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
