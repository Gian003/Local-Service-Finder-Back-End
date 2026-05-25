<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    // Get current worker profile
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Update worker profile
    public function update(Request $request)
    {
        $worker = $request->user();

        $request->validate([
            'first_name'   => 'sometimes|string|max:200',
            'last_name'    => 'sometimes|string|max:200',
            'description'  => 'sometimes|string',
            'category'     => 'sometimes|string',
            'is_available' => 'sometimes|boolean',
            'profile_photo' => 'sometimes|string',
        ]);

        $worker->update($request->only([
            'first_name',
            'last_name',
            'description',
            'category',
            'is_available',
            'profile_photo',
        ]));

        return response()->json([
            'message' => 'Profile updated',
            'worker'  => $worker,
        ]);
    }

    // Toggle availability
    public function toggleAvailability(Request $request)
    {
        $worker = $request->user();
        $worker->update([
            'is_available' => !$worker->is_available,
        ]);

        return response()->json([
            'message'      => 'Availability updated',
            'is_available' => $worker->is_available,
        ]);
    }

    // Get worker's own services
    public function myServices(Request $request)
    {
        $services = $request->user()
            ->services()
            ->where('is_active', true)
            ->get();

        return response()->json($services);
    }

    // Add a service
    public function addService(Request $request)
    {
        $request->validate([
            'title'            => 'required|string',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric',
            'category'         => 'required|string',
            'discount_percent' => 'nullable|numeric',
        ]);

        $service = $request->user()->services()->create([
            'title'            => $request->input('title'),
            'description'      => $request->input('description'),
            'price'            => $request->input('price'),
            'category'         => $request->input('category'),
            'discount_percent' => $request->input('discount_percent'),
            'is_active'        => true,
        ]);

        return response()->json($service, 201);
    }

    // Delete a service
    public function deleteService(Request $request, $id)
    {
        $service = $request->user()
            ->services()
            ->find($id);

        if (!$service) {
            return response()->json([
                'message' => 'Service not found'
            ], 404);
        }

        $service->update(['is_active' => false]);

        return response()->json([
            'message' => 'Service deleted'
        ]);
    }
}
