<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'estimated_hours'
    ];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
        'estimated_hours' => 'float'
    ];

    protected $dates = ['due_date'];

    // Relationship with Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
?>
