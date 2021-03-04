<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ToDo extends Model
{
    protected $fillable = ['id_user', 'title', 'description', 'done'];
}
