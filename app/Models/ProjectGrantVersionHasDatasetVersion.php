<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\ProjectGrantVersionHasDatasetVersionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProjectGrantVersionHasDatasetVersionObserver::class])]
class ProjectGrantVersionHasDatasetVersion extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Prunable;

    public $timestamps = false;

    protected $fillable = [
        'project_grant_version_id',
        'dataset_version_id',
        'link_type',
        'description',
    ];

    protected $dates = ['deleted_at'];

    protected $table = 'project_grant_version_has_dataset_version';
}
