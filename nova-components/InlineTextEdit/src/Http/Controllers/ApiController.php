<?php

namespace Castimize\InlineTextEdit\Http\Controllers;

use App\Models\OrderQueue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController.
 *
 * @package Castimize\InlineTextEdit\Http\Controllers
 * @date    06/05/2024
 */
class ApiController extends Controller
{
    /**
     * Load system settings.
     *
     * @param NovaRequest $request
     */
    public function update(NovaRequest $request)
    {
        $modelId = $request->id;
        $modelClass = $request->model;
        $column = $request->column;
        $value = $request->value;

        $model = null;
        try {
            $model = app($modelClass)->find($modelId);
            $model->$column = $value;
            $model->save();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
