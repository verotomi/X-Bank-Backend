<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Atms extends Model
{
  protected $table = "atms";
  public $timestamps = false;
  protected $guarded = ['id'];
}
