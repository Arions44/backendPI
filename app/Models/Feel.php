<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feel extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'emotion_id', 'date', 'deleted'
    ];
	
    public function emotion(): BelongsTo {
    	return $this->belongsTo(Emotion::class, 'emotion_id');
    }

    // public function business(){
    // 	return $this->belongsTo(Business::class, 'id_business');
    // }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
