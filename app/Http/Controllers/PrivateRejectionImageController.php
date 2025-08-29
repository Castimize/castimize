<?php

namespace App\Http\Controllers;

use App\Models\Rejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PrivateRejectionImageController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $rejection = Rejection::find($id);
        if (! $rejection) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->file(Storage::disk('r2_private')->path($rejection->photo));
    }
}
