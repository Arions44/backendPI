<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enjoy extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id_user', 'id_offer', 'enjoyed', 'enjoyed_at', 'deleted'
    ];

    public function offer(){
    	return $this->belongsTo(Business::class, 'id_offer');
    }
}
