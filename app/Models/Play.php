<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Play extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'game_id', 'deleted'
    ];

    public function game(): BelongsTo {
    	return $this->belongsTo(Game::class, 'game_id');
    }

    // public function business(){
    // 	return $this->belongsTo(Business::class, 'id_business');
    // }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
