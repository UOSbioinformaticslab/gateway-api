<?php

namespace App\Observers;

use App\Models\ProjectGrant;
use App\Models\Publication;
use App\Models\ProjectGrantVersion;
use App\Http\Traits\IndexElastic;
use App\Models\PublicationHasProjectGrantVersion;

class PublicationHasProjectGrantVersionObserver
{
    use IndexElastic;

    /**
     * Handle the PublicationHasProjectGrantVersion "created" event.
     */
    public function created(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion): void
    {
        $this->elasticPublicationHasProjectGrantVersion($publicationHasProjectGrantVersion);
    }

    /**
     * Handle the PublicationHasProjectGrantVersion "updated" event.
     */
    public function updated(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion): void
    {
        $this->elasticPublicationHasProjectGrantVersion($publicationHasProjectGrantVersion);
    }

    /**
     * Handle the PublicationHasProjectGrantVersion "deleted" event.
     */
    public function deleted(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion): void
    {
        $this->elasticPublicationHasProjectGrantVersion($publicationHasProjectGrantVersion);
    }

    /**
     * Handle the PublicationHasProjectGrantVersion "restored" event.
     */
    public function restored(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion): void
    {
        //
    }

    /**
     * Handle the PublicationHasProjectGrantVersion "force deleted" event.
     */
    public function forceDeleted(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion): void
    {
        //
    }

    public function elasticPublicationHasProjectGrantVersion(PublicationHasProjectGrantVersion $publicationHasProjectGrantVersion)
    {
        $publicationId = $publicationHasProjectGrantVersion->publication_id;
        $publication = Publication::where([
            'id' => $publicationId,
            'status' => Publication::STATUS_ACTIVE,
        ])->select('id')->first();

        if (!is_null($publication)) {
            $this->indexElasticPublication((int) $publication->id);
        }

        $projectGrantVersionId = $publicationHasProjectGrantVersion->project_grant_version_id;
        $projectGrantVersion = ProjectGrantVersion::where([
            'id' => $projectGrantVersionId
        ])->select('project_grant_id')->first();

        if (!is_null($projectGrantVersion)) {
            $projectGrant = ProjectGrant::where([
                'id' => $projectGrantVersion->project_grant_id,
                'status' => ProjectGrant::STATUS_ACTIVE,
            ])->select('id', 'team_id')->first();

            if (!is_null($projectGrant)) {
                $this->reindexElasticProjectGrant($projectGrant->id);
                if ($projectGrant->team_id) {
                    $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
                }
            }
        }
    }
}
