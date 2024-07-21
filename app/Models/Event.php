<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

class Event extends Model
{
    use HasFactory, HasTimestamps;

    // The table associated with the model.
    protected $table = 'events';

    // The attributes that are mass assignable.
    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'total_tickets',
        'ticket_price',
        'version'
    ];

    // Define the relationship with the Booking model
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Increment the version number when updating
    public function save(array $options = [])
    {
        if ($this->exists) {
            // Check if version matches
            $originalVersion = $this->getOriginal('version');
            if ($originalVersion !== $this->version) {
                throw new \Exception('The record has been updated by another process.');
            }

            // Increment version number
            $this->version++;
        }

        parent::save($options);
    }
}
