<?php

namespace App\Api\V1\Transformers;

class FormTransformer extends Transformer
{
    /**
     * @param $item
     * @return array
     */
    public function transform($item): array
    {

        return [
            'title' => $item['title'],
            'status' => $item['status'],
            'fields' => $item['fields'],
            'lastUpdate' => $item['updated_at']->toDateTimeString(),
        ];
    }
}