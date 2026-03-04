<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectGrantVersionHasTool extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Prunable;

    protected $fillable = [
        'project_grant_version_id',
        'tool_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $table = 'project_grant_version_has_tools';

    public $timestamps = false;
}
