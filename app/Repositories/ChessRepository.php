<?php

namespace App\Repositories;

use App\Models\Card;
use App\Util\Errors;
use DB;
use Validator;

class ChessRepository
{
    use Errors;

    /**
     * 初始化牌
     * @return mixed
     */
    public function initCards()
    {
        $dictSingle = [];
        for ($i = 1; $i <= 9; $i++) {
            $dictSingle[$i . '|' . Card::CardTypeTong] = Card::CardTypeTong;    //筒
        }
        for ($i = 1; $i <= 9; $i++) {
            $dictSingle[$i . '|' . Card::CardTypeTiao] = Card::CardTypeTiao;    //条
        }
        for ($i = 1; $i <= 9; $i++) {
            $dictSingle[$i . '|' . Card::CardTypeWan] = Card::CardTypeWan;      //万
        }
        for ($i = 1; $i <= 4; $i++) {
            $dictSingle[$i . '|' . Card::CardTypeFeng] = Card::CardTypeFeng;    //东南西北
        }
        for ($i = 1; $i <= 3; $i++) {
            $dictSingle[$i . '|' . Card::CardTypeZi] = Card::CardTypeZi;        //中发白
        }

        //生成一份麻将 每种麻将有4个  (9 * 3 + 4 + 3 ) * 4 = 136
        for ($i = 0; $i <= 135; $i++) {
            $randomArr[] = $i;
        }
        shuffle($randomArr);                 //打乱顺序

        $index = 0;
        for ($times = 0; $times < 4; $times++) {
            foreach ($dictSingle as $key => $value) {
                $tempKeys                      = explode('|', $key);
                $dictWhole[$randomArr[$index]] = [
                    'number'   => $tempKeys[0],
                    'cardType' => $tempKeys[1]
                ];

                ++$index;
            }
        }
        ksort($dictWhole);

        //玩家永远是代码意义上的东边 !!!  todo 但是玩家头像和声音可以换，方位上加个偏移量？？？
        //userDiceSide = Convert.ToInt32(OnlineSide.East);

        //随机掷骰子(谁最先出牌 庄家)  todo 骰子特效
        $randomDiceSide = random_int(1, 4);  // (new System.Random()).Next(1, 5);

        //$nextActivityDiceSide = ($randomDiceSide % 4 + 1) > 4 ? ($randomDiceSide % 4 + 1) % 4 : ($randomDiceSide % 4 + 1); //加1刚好是逆时针轮一位
        $arr = range(0, 135);
        shuffle($arr);

        $randomUsers = array_chunk($arr, 13);

        return $randomDiceSide . '|'
            . implode(',', $randomUsers[0]) . '#' . implode(',', $randomUsers[1]) . '#' . implode(',', $randomUsers[2]) . '#' . implode(',', $randomUsers[3]) . '#'
            . '|' . json_encode($dictWhole);

        return [
            'currentActivityDiceSide' => $randomDiceSide,
            'dictUsers'               => $randomUsers,
            'dictWhole'               => $dictWhole
        ];
    }
}
