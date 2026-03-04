<?php

namespace App\Observers;

use App\Models\Dataset;
use App\Models\ProjectGrant;
use App\Models\DatasetVersion;
use App\Models\ProjectGrantVersion;
use App\Http\Traits\IndexElastic;
use App\Models\ProjectGrantVersionHasDatasetVersion;

class ProjectGrantVersionHasDatasetVersionObserver
{
    use IndexElastic;

    /**
     * Handle the ProjectGrantVersionHasDatasetVersion "created" event.
     */
    public function created(ProjectGrantVersionHasDatasetVersion $link): void
    {
        $this->elasticProjectGrantVersionHasDatasetVersion($link);
    }

    /**
     * Handle the ProjectGrantVersionHasDatasetVersion "updated" event.
     */
    public function updated(ProjectGrantVersionHasDatasetVersion $link): void
    {
        $this->elasticProjectGrantVersionHasDatasetVersion($link);
    }

    /**
     * Handle the ProjectGrantVersionHasDatasetVersion "deleted" event.
     */
    public function deleted(ProjectGrantVersionHasDatasetVersion $link): void
    {
        $this->elasticProjectGrantVersionHasDatasetVersion($link);
    }

    /**
     * Handle the ProjectGrantVersionHasDatasetVersion "restored" event.
     */
    public function restored(ProjectGrantVersionHasDatasetVersion $link): void
    {
        //
    }

    /**
     * Handle the ProjectGrantVersionHasDatasetVersion "force deleted" event.
     */
    public function forceDeleted(ProjectGrantVersionHasDatasetVersion $link): void
    {
        //
    }

    public function elasticProjectGrantVersionHasDatasetVersion(ProjectGrantVersionHasDatasetVersion $link)
    {
        // Reindex the associated Project Grant
        $projectGrantVersion = ProjectGrantVersion::where([
            'id' => $link->project_grant_version_id
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

        // Reindex the associated Dataset
        $datasetVersion = DatasetVersion::where([
            'id' => $link->dataset_version_id
        ])->select('dataset_id')->first();

        if (!is_null($datasetVersion)) {
            $dataset = Dataset::where([
                'id' => $datasetVersion->dataset_id,
                'status' => Dataset::STATUS_ACTIVE,
            ])->select('id', 'team_id')->first();

            if (!is_null($dataset)) {
                $this->reindexElastic($dataset->id);
                if ($dataset->team_id) {
                    $this->reindexElasticDataProviderWithRelations((int) $dataset->team_id, 'dataset');
                }
            }
        }
    }
}
