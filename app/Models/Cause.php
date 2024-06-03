<?php
namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\CauseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cause extends Model
{
    // use CauseFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'mood_id', 'event_id', 'deleted'
    ];
	
    public function mood(): BelongsTo {
    	return $this->belongsTo(Mood::class, 'mood_id');
    }

    // public function business(){
    // 	return $this->belongsTo(Business::class, 'id_business');
    // }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
