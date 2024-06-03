<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emotion extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'type', 'name', 'user_id', 'description', 'image', 'date', 'deleted'
    ];

    // public function business(){
    // 	return $this->belongsTo(Business::class, 'id_business');
    // }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
