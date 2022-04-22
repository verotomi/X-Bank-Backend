<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
  public $timestamps = true;
  protected $table = "tokens";
  protected $fillable = ['updated_at', 'token'];
  protected $guarded = ['id'];
}
