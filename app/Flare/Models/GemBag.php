<?php

namespace App\Flare\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GemBag extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id',
        'gem_id',
        'amount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'character_id' => 'integer',
        'gem_id'       => 'integer',
        'amount'       => 'integer',
    ];

    public function gemSlots(): HasMany {
        return $this->hasMany(GemBagSlot::class);
    }

    public function gem(): BelongsTo {
        return $this->belongsTo(Gem::class, 'gem_id', 'id');
    }

    public function character(): BelongsTo {
        return $this->belongsTo(Character::class, 'character_id', 'id');
    }
}
