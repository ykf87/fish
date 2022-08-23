<?php

if (!function_exists('id_random_code')) {
    /**
     * 根据 ID 生成指定长度随机码
     * 当第一个参数为字符串时，支持解码，且不需要传入第二个参数
     *
     * @param  int|string $idOrStr
     * @param  int        $length
     * @return mixed
     */
    function id_random_code($idOrStr, $length = 6)
    {
        $changeMap = [
            '1' => 'E',
            '2' => 'C',
            '3' => '9',
            '4' => 'D',
            '5' => 'F',
            '6' => '2',
            '7' => 'B',
            '8' => '1',
            '9' => '6',
            '0' => '5',
            'A' => '0',
            'B' => '3',
            'C' => '7',
            'D' => '4',
            'E' => '8',
            'F' => 'A',
        ];
        $nullArr = [
            'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        ];

        if (is_int($idOrStr)) {
            $id      = $idOrStr;
            $baseStr = dechex($id);
            $fullStr = strtoupper(str_pad($baseStr, $length, '?', STR_PAD_BOTH));
            $newStr  = '';
            foreach (str_split($fullStr) as $value) {
                if (isset($changeMap[$value])) {
                    $newStr .= $changeMap[$value];
                } else {
                    $newStr .= $nullArr[array_rand($nullArr)];
                }
            }
            return $newStr;
        } else {
            $str       = $idOrStr;
            $newArr    = [];
            $changeMap = array_flip($changeMap);
            foreach (str_split($str) as $value) {
                if (!in_array($value, $nullArr)) {
                    $newArr[] = $changeMap[$value];
                }
            }
            $chexStr = implode('', $newArr);
            $id      = hexdec($chexStr);
            return $id;
        }
    }
}
