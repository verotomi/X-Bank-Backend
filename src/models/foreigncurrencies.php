<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class ForeignCurrencies extends Model {
        protected $table = "foreigncurrencies";
        public $timestamps = false;
        protected $fillable = ['id', 'name', 'buy', 'sell', 'validfrom'];
        protected $guarded = ['id'];
    }
