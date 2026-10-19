<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Specialty extends Model
{
    protected $fillable=['name','description'];

    public function doctors(): BelongsTo
    {
        return $this->hasMany(Doctor::class);
    }
}
