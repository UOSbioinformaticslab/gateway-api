<?php

namespace App\Observers;

use App\Models\ProjectGrant;
use App\Models\ProjectGrantVersion;
use App\Http\Traits\IndexElastic;

class ProjectGrantVersionObserver
{
    use IndexElastic;

    /**
     * Handle the ProjectGrantVersion "created" event.
     */
    public function created(ProjectGrantVersion $projectGrantVersion): void
    {
        $this->elasticProjectGrantVersion($projectGrantVersion);
    }

    /**
     * Handle the ProjectGrantVersion "updated" event.
     */
    public function updated(ProjectGrantVersion $projectGrantVersion): void
    {
        $this->elasticProjectGrantVersion($projectGrantVersion);
    }

    /**
     * Handle the ProjectGrantVersion "deleted" event.
     */
    public function deleted(ProjectGrantVersion $projectGrantVersion): void
    {
        $this->elasticProjectGrantVersion($projectGrantVersion);
    }

    /**
     * Handle the ProjectGrantVersion "restored" event.
     */
    public function restored(ProjectGrantVersion $projectGrantVersion): void
    {
        //
    }

    /**
     * Handle the ProjectGrantVersion "force deleted" event.
     */
    public function forceDeleted(ProjectGrantVersion $projectGrantVersion): void
    {
        //
    }

    public function elasticProjectGrantVersion(ProjectGrantVersion $projectGrantVersion)
    {
        $projectGrantId = $projectGrantVersion->project_grant_id;
        $projectGrant = ProjectGrant::where([
            'id' => $projectGrantId,
            'status' => ProjectGrant::STATUS_ACTIVE,
        ])->select('id', 'status', 'team_id')->first();

        if (!is_null($projectGrant) && $projectGrant->status === ProjectGrant::STATUS_ACTIVE && $projectGrantVersion->active_date === null) {
            $projectGrantVersion->active_date = now();
            $projectGrantVersion->save();
        } elseif (!is_null($projectGrant) && $projectGrant->status === ProjectGrant::STATUS_ACTIVE) {

            $this->reindexElasticProjectGrant($projectGrant->id);
            if ($projectGrant->team_id) {
                $this->reindexElasticDataProviderWithRelations((int) $projectGrant->team_id, 'project_grant');
            }
        }
    }
}
