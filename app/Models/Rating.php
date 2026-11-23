<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'rating', 'comment'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
?>
