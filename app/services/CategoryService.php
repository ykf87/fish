<?php

namespace App\services;

use App\Models\Category;

class CategoryService
{
    public function getListByMark($mark, $type = '')
    {
        $data = [];
        $id = Category::where('mark', $mark)->value('id');
        if (!$id) {
            return $data;
        }

        $data = Category::query()->select('id', 'title')
            ->where('parent_id', $id)
            ->orderByDesc('order')
            ->get();

        if ($type == 'pluck') {
            $data = $data->pluck('title', 'id');
        }

        return $data->toArray();
    }

}