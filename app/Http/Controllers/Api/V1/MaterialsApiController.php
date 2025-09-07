<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Services\Admin\LogRequestService;

class MaterialsApiController extends ApiController
{
    public function __construct() {}

    public function index()
    {
        $materials = Material::all();

        return MaterialResource::collection($materials);
    }

    public function show(Material $material): MaterialResource
    {
        $response = new MaterialResource($material);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }
}
