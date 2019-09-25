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
            'content' => $item['content'],
            'logo' => $item['logo'],
            'parent_id' => $item['parent_id'],
            'lastUpdate' => $item['updated_at']->toDateTimeString(),
        ];
    }
}