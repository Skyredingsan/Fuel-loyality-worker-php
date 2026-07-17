<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Domain\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int             $id
 * @property string          $code         ПМ | ОЭК | ЭКЛ | КБ
 * @property string          $name
 * @property string|null     $description
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 *
 * @property Collection<int, KpiIndicator> $indicators
 */
class KpiCategory extends Model
{
    protected $table = 'kpi_categories';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * @return HasMany<KpiIndicator>
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(KpiIndicator::class, 'category_id');
    }
}