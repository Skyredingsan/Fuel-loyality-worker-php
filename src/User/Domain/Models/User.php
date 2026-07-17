<?php

declare(strict_types=1);

namespace FuelPoints\User\Domain\Models;

use Carbon\Carbon;
use FuelPoints\Level\Domain\Models\UserLevelHistory;
use FuelPoints\Result\Domain\Models\MonthlyResult;
use FuelPoints\User\Domain\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @property int         $id
 * @property string      $email
 * @property string      $password_hash
 * @property UserRole    $role
 * @property string      $fio
 * @property string|null $cluster_name
 * @property int         $azs_count
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password_hash',
        'role',
        'fio',
        'cluster_name',
        'azs_count',
    ];

    protected $hidden = [
        'password_hash',
        'deleted_at',
    ];

    protected $casts = [
        'role'      => UserRole::class,
        'azs_count' => 'integer',
    ];

    /**
     * Результаты, заведённые этим пользователем (как эксперт).
     *
     * @return HasMany<MonthlyResult>
     */
    public function enteredResults(): HasMany
    {
        return $this->hasMany(MonthlyResult::class, 'expert_id');
    }

    /**
     * Собственные результаты ТМ.
     *
     * @return HasMany<MonthlyResult>
     */
    public function monthlyResults(): HasMany
    {
        return $this->hasMany(MonthlyResult::class, 'user_id');
    }

    /**
     * @return HasMany<UserLevelHistory>
     */
    public function levelHistory(): HasMany
    {
        return $this->hasMany(UserLevelHistory::class, 'user_id');
    }

    /**
     * Псевдо-атрибут для совместимости с Laravel guard.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Указываем factory для модели (пространства имён не совпадают по умолчанию).
     */
    protected static function newFactory(): \Database\Factories\UserFactory
    {
        return \Database\Factories\UserFactory::new();
    }
}