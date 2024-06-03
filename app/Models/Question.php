<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'text', 'answer_1', 'answer_2', 'answer_3', 'answer_4', 'deleted'
    ];

    // public function business(){
    // 	return $this->belongsTo(Business::class, 'id_business');
    // }

    // public function enjoys(): HasMany
    // {
    //     return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    // }
}
