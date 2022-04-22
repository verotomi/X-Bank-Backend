<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class SavingTypes extends Model
{
  protected $table = "saving_types";
  public $timestamps = false;
  protected $fillable = ['id', 'type', 'rate', 'duration', 'currency'];
  protected $guarded = ['id'];
}
