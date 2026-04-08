<?php

namespace Modules\Auth\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCredentials extends Model
{
    use HasFactory;

    protected $table = 'usercredentials';

    protected $fillable = [
        'userId',
        'login'
    ];

    protected $hidden = [
        'password',
    ];
}
