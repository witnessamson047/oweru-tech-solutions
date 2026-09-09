<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageExclusion extends Model
{
    protected $fillable = ['description', 'details', 'sort_order', 'active'];
    protected $casts = ['active' => 'boolean'];
}
