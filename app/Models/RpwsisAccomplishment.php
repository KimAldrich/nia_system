<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpwsisAccomplishment extends Model
{
    protected $guarded = [];
    protected $table = 'rpwsis_accomplishments'; // ✅ important

protected $fillable = [
    'region','batch','allocation','nis','activity','remarks','amount',
    'c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12',
    'phy','fin','exp'
];
}
