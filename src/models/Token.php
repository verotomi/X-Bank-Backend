<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model{
    //ha created_at / updated_at nélkül hoztuk létre:
    public $timestamps = false;
}