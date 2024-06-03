<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'message', 'opt_1', 'opt_2', 'opt_3', 'opt_4', 'right_opt', 'deleted'
    ];

    public function mood1(){
    	return $this->belongsTo(Mood::class, 'opt_1');
    }

    public function mood2(){
    	return $this->belongsTo(Mood::class, 'opt_2');
    }

    public function mood3(){
    	return $this->belongsTo(Mood::class, 'opt_3');
    }

    public function mood4(){
    	return $this->belongsTo(Mood::class, 'opt_4');
    }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
