<?php

namespace App\Models;

use DB;
use App\Models\Traits\EntityCounter;
use App\Observers\ProjectGrantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(ProjectGrantObserver::class)]
class ProjectGrant extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Prunable;
    use EntityCounter;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $table = 'project_grants';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'team_id',
        'projectgrantid',
        'created',
        'updated',
        'submitted',
        'pid',
        'version',
        'status',
    ];

    /**
     * @return HasMany<ProjectGrantVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ProjectGrantVersion::class, 'project_grant_id');
    }

    public function latestVersion(?array $fields = null): ProjectGrantVersion | null
    {
        $version = ProjectGrantVersion::where('project_grant_id', $this->id)
            ->select(['version', 'id'])
            ->latest('version')
            ->first();

        if (!$version) {
            return null;
        }

        return ProjectGrantVersion::when(
            $fields,
            function ($query, $fields) {
                return $query->select($fields);
            }
        )->findOrFail($version->id);
    }

    /** @return HasOne<ProjectGrantVersion, $this> */
    public function latestMetadata(): HasOne
    {
        return $this->hasOne(ProjectGrantVersion::class, 'project_grant_id')
            ->withTrashed()
            ->orderBy('version', 'desc');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    // Accessors for many-to-many relationships routed through the version
    public function getAllPublicationsAttribute()
    {
        return $this->getRelationsViaVersion(
            PublicationHasProjectGrantVersion::class,
            Publication::class,
            'publication_id'
        );
    }

    public function getAllToolsAttribute()
    {
        return $this->getRelationsViaVersion(
            ProjectGrantVersionHasTool::class,
            Tool::class,
            'tool_id'
        );
    }

    public function getAllDatasetsAttribute()
    {
        return $this->getRelationsViaVersion(
            ProjectGrantVersionHasDatasetVersion::class,
            DatasetVersion::class,
            'dataset_version_id'
        );
    }

    /**
     * Helper function to get linked entities across all versions
     */
    public function getRelationsViaVersion($linkageTable, $targetTable, $foreignTableId)
    {
        $versionIds = $this->versions()->pluck('id')->toArray();

        $linkageRecords = $linkageTable::whereIn('project_grant_version_id', $versionIds)
            ->get([$foreignTableId, 'project_grant_version_id']);

        $entityIds = $linkageRecords->pluck($foreignTableId)->unique()->toArray();

        $entities = $targetTable::whereIn('id', $entityIds)->get();

        foreach ($entities as $entity) {
            $filteredLinkage = $linkageRecords->where($foreignTableId, $entity->id);
            $versionIdsLinked = $filteredLinkage->pluck('project_grant_version_id')->toArray();
            $entity->setAttribute('project_grant_version_ids', $versionIdsLinked);
        }

        return $entities->toArray();
    }
}
