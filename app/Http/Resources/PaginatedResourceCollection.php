<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedResourceCollection extends ResourceCollection
{
    private $resourceClass;
    private $data;
    public function __construct($resourceClass, $data)
    {
        parent::__construct($data);
        $this->resourceClass = $resourceClass;
        $this->data = $data;
    }
    public function toArray(Request $request): array
    {
        return [
            'current_page' => $this->data->currentPage(),
            'data' => $this->resourceClass::collection($this->data->items()),
            'first_page_url' => $this->data->url(1),
            'from' => $this->data->firstItem(),
            'last_page' => $this->data->lastPage(),
            'last_page_url' => $this->data->url($this->data->lastPage()),
            'links' => $this->data->linkCollection(),
            'next_page_url' => $this->data->nextPageUrl(),
            'path' => $this->data->path(),
            'per_page' => $this->data->perPage(),
            'prev_page_url' => $this->data->previousPageUrl(),
            'to' => $this->data->lastItem(),
            'total' => $this->data->total(),
        ];
    }
}
