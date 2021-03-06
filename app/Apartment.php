<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{

    protected $fillable = ['title', 'rooms_number', 'beds_number', 'bathrooms_number', 'square_meters', 'latitude', 'longitude', 'image', 'visibility', 'city', 'address', 'zip_code', 'slug', 'description', 'user_id'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function facilities() {
        return $this->belongsToMany('App\Facility');
    }

    public function views() {
        return $this->hasMany('App\View');
    }

    public function ads() {
        return $this->belongsToMany('App\Ad');
    }
    
    public function messages() {
        return $this->hasMany('App\Message');
    }
}
