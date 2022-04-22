<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
{
  protected $table = "currencies";
  public $timestamps = false;
  protected $fillable = ['id', 'name', 'buy', 'sell', 'validfrom'];
  protected $guarded = ['id'];
}
