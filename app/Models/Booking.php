<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // The table associated with the model.
    protected $table = 'bookings';

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id',
        'event_id',
        'tickets_booked',
        'unit_price', 
        'total_price'
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
