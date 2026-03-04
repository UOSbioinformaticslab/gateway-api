<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProjectGrantVersionObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(ProjectGrantVersionObserver::class)]
class ProjectGrantVersion extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Prunable;

    protected $table = 'project_grant_versions';

    public $timestamps = true;

    protected $fillable = [
        'project_grant_id',
        'version',
        'pid',
        'projectGrantName',
        'leadResearcher',
        'leadResearchInstitute',
        'grantNumber',
        'projectGrantStartDate',
        'projectGrantEndDate',
        'projectGrantScope'
    ];

    protected $casts = [
        'grantNumbers' => 'array',
        'projectGrantStartDate' => 'date',
        'projectGrantEndDate' => 'date',
    ];

    public function projectGrant(): BelongsTo
    {
        return $this->belongsTo(ProjectGrant::class, 'project_grant_id', 'id')
            ->where('status', ProjectGrant::STATUS_ACTIVE)
            ->select(['id', 'status']);
    }

    public function datasets(): BelongsToMany
    {
        return $this->belongsToMany(
            DatasetVersion::class,
            'project_grant_version_has_dataset_version',
            'project_grant_version_id',
            'dataset_version_id'
        );
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'publication_has_project_grant_version',
            'project_grant_version_id',
            'publication_id'
        );
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(
            Tool::class,
            'project_grant_version_has_tool',
            'project_grant_version_id',
            'tool_id'
        );
    }
}
