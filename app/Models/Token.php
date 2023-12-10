<?php

namespace App\Models;
use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    #TODO: this class needs to be deleted!

    public function customer()
    {
        return $this->hasOne(Employee::class,'id', 'user_id' );
    }
}
