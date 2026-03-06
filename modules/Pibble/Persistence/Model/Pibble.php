<?php

namespace Modules\Pibble\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pibble extends Model
{
    use HasFactory;

    protected $table = strtolower('Pibbles');

    protected $fillable = [
        "name",
        "bellyWashed"
    ];
}
