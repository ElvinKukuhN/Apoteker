<?php

namespace App;

use App\Traits\MultiTenantModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;

class Transaction extends Model
{
    use  MultiTenantModelTrait;

    public $table = 'transactions';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'stock',
        'team_id',
        'user_id',
        'asset_id',
        'alat_id',
        'disposable_id',
        'is_transaction',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');

    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');

    }

    public function disposable()
    {
        return $this->belongsTo(Disposable::class, 'disposable_id');

    }

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');

    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');

    }
}
