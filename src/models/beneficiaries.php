<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class Beneficiaries extends Model {
        protected $table = "beneficiaries";
        public $timestamps = false;
        protected $fillable = ['id', 'id_user', 'name', 'partner_name', 'partner_account_number', 'status', 'created_on'];
        protected $guarded = ['id'];    
    }
