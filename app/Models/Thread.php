<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
