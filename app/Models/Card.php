<?php

namespace App\Models;

class Card extends \Illuminate\Foundation\Auth\User
{
    //是否是机器人 0-否 1-是
    const CardTypeTong = 0;
    const CardTypeTiao = 1;
    const CardTypeWan = 2;
    const CardTypeFeng = 3;
    const CardTypeZi = 4;

}
