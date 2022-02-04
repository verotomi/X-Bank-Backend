<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class CreditCards extends Model {
        protected $table = "credit_cards";
        public $timestamps = false;
        protected $fillable = ['id', 'id_user', 'id_bank_account', 'number', 'type', 'name_on_card', 'expire_date', 'cvc', 'status', 'limit_atm', 'limit_pos', 'limit_online'];
        protected $guarded = ['id'];
    }
