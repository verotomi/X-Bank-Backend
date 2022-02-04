<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class Users extends Model {
        protected $table = "users";
        public $timestamps = false;
        protected $fillable = ['id', 'firstname', 'lastname', 'mobilebank_id', 'pincode', 'password', 'created_on', 'last_login'];
        protected $guarded = ['id'];
        //protected $visible = ['id', 'firstname', 'lastname', 'mobilebank_id', 'pincode', 'password', 'created_on', 'last_login'];

        /*public function bankaccount(){
            //return $this->hasOne('Vero\Xbank\Models\bankaccounts', 'id_user');
            return $this->hasOne(BankAccounts::class, 'id_user');
        }*/
    }
