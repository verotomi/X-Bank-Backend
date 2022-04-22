<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class BankAccounts extends Model
{
  protected $table = "bank_accounts";
  public $timestamps = false;
  protected $fillable = ['id', 'id_user', 'number', 'type', 'currency', 'balance', 'status', 'created_on'];
  protected $guarded = ['id'];
}
