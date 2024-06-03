<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id_business', 'type', 'name', 'discount', 'description', 'last_description', 'completeDescription', 'image', 'counter', 'category', 'deleted'
    ];

    public function business(){
    	return $this->belongsTo(Business::class, 'id_business');
    }

    public function enjoys(): HasMany
    {
        return $this->hasMany(Enjoy::class, 'id_offer', 'id');
    }
}
