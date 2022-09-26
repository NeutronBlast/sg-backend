<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, int $id)
 */
class Participation extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'participation_date'
    ];

    protected $primaryKey = 'id';
    public $timestamps = false;

    public function users(){
        return $this->hasOne(User::class);
    }
}
