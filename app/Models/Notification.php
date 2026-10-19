<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable=['message','type','is_read'];

    protected $casts = [
        'data' => 'array', // تبدیل  JSONB به آرایه
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
