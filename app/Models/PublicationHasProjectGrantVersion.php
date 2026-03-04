<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\PublicationHasProjectGrantVersionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PublicationHasProjectGrantVersionObserver::class])]
class PublicationHasProjectGrantVersion extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Prunable;

    public $timestamps = false;

    protected $fillable = [
        'publication_id',
        'project_grant_version_id',
        'link_type',
        'description',
    ];

    protected $dates = ['deleted_at'];

    protected $table = 'publication_has_project_grant_version';
}
