<?php

namespace Modules\Pibble\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pibble extends Model
{
    use HasFactory;

    protected $table = 'pibbles';

    protected $fillable = ['name', 'belly_washed'];
}
