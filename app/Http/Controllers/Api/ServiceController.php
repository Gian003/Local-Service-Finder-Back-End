<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('worker')->where('is_active', true);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'popular':
                    $query->orderBy('review_count', 'desc');
                    break;
                case 'lowest_price':
                case 'price-asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'most_expensive':
                case 'price-desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $service = Service::with('worker')->find($id);

        if (!$service) {
            return response()->json([
                'message' => 'service not found'
            ], 404);
        };

        return response()->json($service);
    }
}
