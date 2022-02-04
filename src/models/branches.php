<?php
    namespace Models;

    use Illuminate\Database\Eloquent\Model;

    class Branches extends Model {
        protected $table = "branches";
        public $timestamps = false;
        //protected $fillable = ['id', 'name', 'buy', 'sell', 'validfrom'];
        protected $guarded = ['id'];
    }
