<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class DailyAccountBalances extends Model
{
  protected $table = "daily_account_balances";
  public $timestamps = false;
  protected $fillable = ['id', 'id_bank_account_number', 'closing_balance', 'date', 'generated_on'];
  protected $guarded = ['id'];
}
