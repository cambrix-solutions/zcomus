<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §9, items 47–51.
 */
class AddressController extends Controller
{
    use ApiResponds;

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->orderByDesc('id')->get();

        return $this->respond(AddressResource::collection($addresses)->toArray($request));
    }

    /**
     * POST /api/addresses
     * If is_default is true, unsets it on every other address first —
     * "the default address" only makes sense as a single one at a time.
     */
    public function store(AddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'KH';

        if ($data['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($data);

        return $this->respond(new AddressResource($address), 'Address created.', 201);
    }

    /**
     * PUT /api/addresses/{id}
     */
    public function update(AddressRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->find($id);

        if (! $address) {
            return $this->respondMessage('Address not found.', 404);
        }

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return $this->respond(new AddressResource($address->fresh()));
    }

    /**
     * DELETE /api/addresses/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $request->user()->addresses()->where('id', $id)->delete();

        if (! $deleted) {
            return $this->respondMessage('Address not found.', 404);
        }

        return $this->respondMessage('Address deleted.');
    }

    /**
     * PUT /api/addresses/{id}/default
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->find($id);

        if (! $address) {
            return $this->respondMessage('Address not found.', 404);
        }

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return $this->respond(new AddressResource($address->fresh()));
    }
}
