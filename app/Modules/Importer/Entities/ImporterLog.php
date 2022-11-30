<?php

namespace Modules\Importer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImporterLog extends Model
{
    use HasFactory;

    protected $table = 'importer_log';

    protected $fillable = [
        'type',
        'run_at',
        'entries_processed',
        'entries_created',
    ];
}
