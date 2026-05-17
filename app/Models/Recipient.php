<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'name',
    ];

    /**
     * Уведомления получателя
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
