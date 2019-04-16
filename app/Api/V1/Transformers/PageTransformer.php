<?php

namespace App\Api\V1\Transformers;

class PageTransformer extends Transformer
{
    public function transform($item)
    {
        return [
            'id' => $item['id'],
            'title' => $item['title'],
            'status' => $item['status'],
            'description' => $item['description'],
            'lastUpdate' => $item['updated_at']->toDateTimeString(),
        ];
    }
}