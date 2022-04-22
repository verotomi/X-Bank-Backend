<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Statements extends Model
{
  protected $table = "account_statements";
  public $timestamps = false;
  protected $fillable = ['id', 'id_user', 'id_bank_account', 'number', 'filename'];
  protected $guarded = ['id'];
}
