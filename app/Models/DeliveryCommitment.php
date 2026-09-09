<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCommitment extends Model
{
    protected $fillable = ['title', 'description', 'sort_order', 'active'];
    protected $casts = ['active' => 'boolean'];
}
