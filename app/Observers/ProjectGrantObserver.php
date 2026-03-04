<?php

namespace App\Observers;

use App\Models\ProjectGrant;
use App\Http\Traits\IndexElastic;
use App\Models\ProjectGrantVersion;

class ProjectGrantObserver
{
    use IndexElastic;

    /**
     * Handle the ProjectGrant "created" event.
     */
    public function created(ProjectGrant $projectGrant): void
    {
        $projectGrantVersion = ProjectGrantVersion::where([
            'project_grant_id' => $projectGrant->id
        ])->select('id')->first();

        if ($projectGrant->status === ProjectGrant::STATUS_ACTIVE && !is_null($projectGrantVersion)) {
            $this->reindexElasticProjectGrant($projectGrant->id);
            if ($projectGrant->team_id) {
                $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
            }
        }
    }

    /**
     * Handle the ProjectGrant "updating" event.
     */
    public function updating(ProjectGrant $projectGrant)
    {
        $projectGrant->prevStatus = $projectGrant->getOriginal('status');
    }

    /**
     * Handle the ProjectGrant "updated" event.
     */
    public function updated(ProjectGrant $projectGrant): void
    {
        $prevStatus = $projectGrant->prevStatus;
        $projectGrantVersion = ProjectGrantVersion::where([
            'project_grant_id' => $projectGrant->id
        ])->select('id')->first();

        if ($prevStatus === ProjectGrant::STATUS_ACTIVE && $projectGrant->status !== ProjectGrant::STATUS_ACTIVE) {
            $this->deleteProjectGrantFromElastic($projectGrant->id);
            if ($projectGrant->team_id) {
                $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
            }
        }

        if ($projectGrant->status === ProjectGrant::STATUS_ACTIVE && !is_null($projectGrantVersion)) {
            $this->reindexElasticProjectGrant($projectGrant->id);
            if ($projectGrant->team_id) {
                $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
            }
        }
    }

    /**
     * Handle the ProjectGrant "deleting" event.
     */
    public function deleting(ProjectGrant $projectGrant)
    {
        $projectGrant->prevStatus = $projectGrant->getOriginal('status');
    }

    /**
     * Handle the ProjectGrant "deleted" event.
     */
    public function deleted(ProjectGrant $projectGrant): void
    {
        $prevStatus = $projectGrant->prevStatus;

        if ($prevStatus === ProjectGrant::STATUS_ACTIVE) {
            $this->deleteProjectGrantFromElastic($projectGrant->id);
            if ($projectGrant->team_id) {
                $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
            }
        }
    }

    /**
     * Handle the ProjectGrant "restored" event.
     */
    public function restored(ProjectGrant $projectGrant): void
    {
        //
    }

    /**
     * Handle the ProjectGrant "force deleted" event.
     */
    public function forceDeleted(ProjectGrant $projectGrant): void
    {
        //
    }
}
