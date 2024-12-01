<?php

namespace App\Http\Controllers;

use App\Models\Creature;
use App\Enums\CreatureRaceEnum;
use App\Enums\CreatureTypeEnum;
use App\Http\Requests\CreatureStoreRequest;
use App\Http\Requests\CreatureUpdateRequest;
use App\Http\Requests\CreatureDestroyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CreatureController extends Controller
{
    public function index()
    {
        return response()->json(Creature::all());
    }

    public function store(CreatureStoreRequest $request)
    {
        $formFields = $request->validated();
        $creature = new Creature();
        $creature->user_id = Auth::user()->id;
        $creature->fill($formFields);

        if ($request->file('avatar_blob')) {
            $fileName = time() . '_' . $request->avatar_blob->getClientOriginalName();
            $creature->avatar = $fileName;
            $request->avatar_blob->move(public_path('images/uploads'), $fileName);
        }

        $creature->save();
        return response()->json($creature);
    }

    public function show(string $id)
    {
        $creature = Creature::with('user')->find($id);
        return response()->json($creature);
    }

    public function update(CreatureUpdateRequest $request, Creature $creature)
    {
        $formFields = $request->validated();
        $creature->fill($formFields);

        if ($request->file('avatar_blob') && File::exists(public_path('images/uploads/' . $creature->avatar))) {
            File::delete(public_path('images/uploads/' . $creature->avatar));
        }

        if ($request->file('avatar_blob')) {
            $fileName = time() . '_' . $request->avatar_blob->getClientOriginalName();
            $creature->avatar = $fileName;
            $request->avatar_blob->move(public_path('images/uploads'), $fileName);
        }

        $creature->save();
        return response()->json($creature);
    }

    public function destroy(CreatureDestroyRequest $request, Creature $creature)
    {
        $request->validated();
        if (File::exists(public_path('images/uploads/' . $creature->avatar))) {
            File::delete(public_path('images/uploads/' . $creature->avatar));
        }

        $creature->delete();
        return response()->json(['count' => Creature::count()]);
    }

    public function getTypes()
    {
        return response()->json(CreatureTypeEnum::values());
    }

    public function getRaces()
    {
        return response()->json(CreatureRaceEnum::values());
    }

    public function search(Request $request)
    {
        $page = $request->query('page', 1);
        $name = $request->query('name');
        $minPv = $request->query('minPv');
        $maxPv = $request->query('maxPv');
        $types = $request->query('types');
        $races = $request->query('races');
        $orderBy = $request->query('orderBy');
        $orderType = $request->query('orderType');
        $limit = $request->query('limit', 3);

        $results = Creature::search(
            name: $name,
            minPv: $minPv,
            maxPv: $maxPv,
            types: $types,
            races: $races,
            page: $page,
            limit: $limit,
            orderBy: $orderBy,
            orderType: $orderType
        );

        $maxPages = ceil($results['totalResults'] / $limit);

        return response()->json([
            'creatures' => $results['creatures'],
            'maxPages' => $maxPages,
            'page' => $page
        ]);
    }
}