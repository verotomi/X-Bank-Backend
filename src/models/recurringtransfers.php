<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class RecurringTransfers extends Model {
        protected $table = "recurring_transfers";
        public $timestamps = false;
        protected $fillable = ['id', 'id_user', 'id_bank_account_number', 'name', 'direction', 'type', 'reference_number', 'currency', 'amount', 'partner_name', 'partner_account_number', 'comment', 'arrived_on', 'status'];
        protected $guarded = ['id'];
    }
