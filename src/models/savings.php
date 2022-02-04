<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class Savings extends Model {
        protected $table = "savings";
        public $timestamps = false;
        protected $fillable = ['id', 'id_user', 'id_bank_account', 'id_type', 'expire_date', 'status', 'reference_number', 'arrived_on'];
        protected $guarded = ['id'];
    }
