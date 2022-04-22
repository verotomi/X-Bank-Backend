<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
  protected $table = "users";
  public $timestamps = false;
  protected $fillable = ['id', 'firstname', 'lastname', 'mobilebank_id', 'pincode', 'password', 'created_on', 'last_login'];
  protected $guarded = ['id'];
}
