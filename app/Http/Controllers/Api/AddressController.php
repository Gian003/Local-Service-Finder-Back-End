<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    // Get all addresses for current user
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($addresses);
    }

    // Add new address
    public function store(Request $request)
    {
        $request->validate([
            'label'      => 'required|string|max:50',
            'address'    => 'required|string|max:255',
            'city'       => 'required|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);

        // If new address is default, unset others
        if ($request->input('is_default', false)) {
            Address::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id'    => $request->user()->id,
            'label'      => $request->input('label'),
            'address'    => $request->input('address'),
            'city'       => $request->input('city'),
            'is_default' => $request->input('is_default', false),
        ]);

        return response()->json([
            'message' => 'Address added successfully',
            'address' => $address,
        ], 201);
    }

    // Update address
    public function update(Request $request, $id)
    {
        $address = Address::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        $request->validate([
            'label'      => 'sometimes|string|max:50',
            'address'    => 'sometimes|string|max:255',
            'city'       => 'sometimes|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);

        // If updating to default, unset others
        if ($request->input('is_default', false)) {
            Address::where('user_id', $request->user()->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($request->only([
            'label',
            'address',
            'city',
            'is_default',
        ]));

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address,
        ]);
    }

    // Set as default
    public function setDefault(Request $request, $id)
    {
        // Unset all defaults first
        Address::where('user_id', $request->user()->id)
            ->update(['is_default' => false]);

        // Set this one as default
        $address = Address::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'Default address updated',
            'address' => $address,
        ]);
    }

    // Delete address
    public function destroy(Request $request, $id)
    {
        $address = Address::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        try {
            $address->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Cannot delete this address because it is linked to an existing booking.',
            ], 422);
        }

        return response()->json([
            'message' => 'Address deleted successfully'
        ]);
    }
}
