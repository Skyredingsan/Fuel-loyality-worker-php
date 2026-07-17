<?php

declare(strict_types=1);

namespace FuelPoints\Report\Domain\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string      $id            UUID
 * @property int         $user_id
 * @property string      $period        YYYY-MM
 * @property string      $status        pending | processing | ready | failed
 * @property string|null $file_path
 * @property int         $rows_count
 * @property string|null $error
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class CsvExport extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $table = 'csv_exports';

    protected $fillable = [
        'id',
        'user_id',
        'period',
        'status',
        'file_path',
        'rows_count',
        'error',
    ];

    protected $casts = [
        'id'         => 'string',
        'user_id'    => 'integer',
        'rows_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model): void {
            if ($model->id === null) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}