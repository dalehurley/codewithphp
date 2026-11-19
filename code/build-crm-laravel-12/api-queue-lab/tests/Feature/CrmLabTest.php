<?php

namespace Tests\Feature;

use App\Jobs\ExportContacts;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrmLabTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_crud_uses_the_token_owners_records(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $otherContact = new Contact(['name' => 'Private', 'email' => 'private@example.test']);
        $otherContact->user_id = $anotherUser->id;
        $otherContact->save();

        $token = $owner->createToken('crm-test', ['contacts:read', 'contacts:write']);

        $this->getJson('/api/v1/contacts')->assertUnauthorized();

        $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/contacts', [
                'name' => 'Avery Chen',
                'email' => 'avery@example.test',
                'user_id' => $anotherUser->id,
            ])->assertCreated()->assertJsonPath('data.email', 'avery@example.test');

        $contact = Contact::where('email', 'avery@example.test')->firstOrFail();
        $this->assertSame($owner->id, $contact->user_id);

        $this->getJson('/api/v1/contacts')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 1);
        $this->getJson("/api/v1/contacts/{$otherContact->id}")->assertNotFound();
        $this->patchJson("/api/v1/contacts/{$otherContact->id}", ['name' => 'Blocked'])->assertNotFound();
        $this->deleteJson("/api/v1/contacts/{$otherContact->id}")->assertNotFound();
        $this->patchJson("/api/v1/contacts/{$contact->id}", ['name' => 'Avery Lin'])
            ->assertOk()->assertJsonPath('data.name', 'Avery Lin');
        $this->postJson('/api/v1/contacts', ['name' => 'Missing email'])->assertUnprocessable();
        $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertNoContent();
    }

    public function test_a_read_token_cannot_write(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('read-only', ['contacts:read']);

        $this->withToken($token->plainTextToken)->getJson('/api/v1/contacts')->assertOk();
        $this->postJson('/api/v1/contacts', [
            'name' => 'Blocked',
            'email' => 'blocked@example.test',
        ])->assertForbidden();
    }

    public function test_retry_reuses_the_path_and_exports_only_owned_contacts(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();

        foreach ([$owner, $other] as $user) {
            $contact = new Contact(['name' => $user->name, 'email' => $user->email]);
            $contact->user_id = $user->id;
            $contact->save();
        }

        $id = (string) Str::uuid();
        ExportContacts::dispatchSync($owner->id, $id);
        ExportContacts::dispatchSync($owner->id, $id);

        $disk = Storage::disk('local');
        $path = "contact-exports/{$owner->id}/{$id}.json";

        $this->assertSame([$path], $disk->allFiles('contact-exports'));
        $data = json_decode($disk->get($path), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([$owner->email], array_column($data, 'email'));
    }
}
