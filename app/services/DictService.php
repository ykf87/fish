<?php

namespace App\services;

use App\Models\Dict;

class DictService
{
    public function info($param)
    {
        $info = Dict::select('type', 'name', 'dict_key', 'dict_val', 'dict_val_json')
            ->where('dict_key', $param['dict_key'])
            ->first();

        if ($info) {
            $info = $info->toArray();
            if ($info['type'] == 'json') {
                $info['dict_val'] = $info['dict_val_json'];
            }
            unset($info['dict_val_json']);
        }

        return $info;
    }

}