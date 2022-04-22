<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
  protected $table = "transactions";
  public $timestamps = false;
  protected $fillable = ['id', 'id_user', 'id_bank_account_number', 'type', 'direction', 'reference_number', 'currency', 'amount', 'partner_name', 'partner_account_number', 'comment', 'arrived_on', 'balance'];
  protected $guarded = ['id'];
}
