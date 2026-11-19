<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContactApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ContactResource::collection($this->contacts($request)->orderBy('id')->paginate(15));
    }

    public function store(Request $request): ContactResource
    {
        $contact = new Contact($this->validated($request));
        $contact->user_id = $request->user()->getKey();
        $contact->save();

        return new ContactResource($contact);
    }

    public function show(Request $request, int $contact): ContactResource
    {
        return new ContactResource($this->contacts($request)->findOrFail($contact));
    }

    public function update(Request $request, int $contact): ContactResource
    {
        $record = $this->contacts($request)->findOrFail($contact);
        $record->update($this->validated($request));

        return new ContactResource($record);
    }

    public function destroy(Request $request, int $contact): Response
    {
        $this->contacts($request)->findOrFail($contact)->delete();

        return response()->noContent();
    }

    private function contacts(Request $request): Builder
    {
        return Contact::query()->where('user_id', $request->user()->getKey());
    }

    private function validated(Request $request): array
    {
        $presence = $request->isMethod('PATCH') ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$presence, 'required', 'string', 'max:255'],
            'email' => [$presence, 'required', 'email', 'max:255'],
        ]);
    }
}
