<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PDOException;
use Tests\TestCase;

class DatabaseValidationResponseTest extends TestCase
{
    public function test_known_uniqueness_conflicts_have_field_errors_without_exposing_sql(): void
    {
        config(['app.debug' => false]);
        $request = Request::create('/admin/users', 'POST');
        $request->headers->set('Accept', 'application/json');
        foreach ([
            'branches_name_unique' => 'name', 'facilities_name_unique' => 'name',
            'floors_floor_number_unique' => 'floor_number', 'rooms_code_unique' => 'code',
            'users_email_unique' => 'email', 'users_email_normalized_unique' => 'email',
        ] as $constraint => $field) {
            $previous = new PDOException('duplicate key');
            $previous->errorInfo = ['23505', 7, 'duplicate key value violates unique constraint "'.$constraint.'"'];
            $exception = new QueryException('pgsql', 'insert into secret_table values (?)', ['sensitive-value'], $previous);
            $response = app(Handler::class)->render($request, $exception);
            $this->assertSame(422, $response->getStatusCode());
            $this->assertArrayHasKey($field, json_decode($response->getContent(), true)['errors']);
            $this->assertStringNotContainsString('sensitive-value', $response->getContent());
            $this->assertStringNotContainsString('secret_table', $response->getContent());
        }
        // Unknown constraints and other SQL failures must not masquerade as bad user input.
        $previous->errorInfo = ['23505', 7, 'unknown_unique'];
        $response = app(Handler::class)->render($request, new QueryException('pgsql', 'sql', [], $previous));
        $this->assertSame(500, $response->getStatusCode());
        $previous->errorInfo = ['23505', 7, 'duplicate key value violates unique constraint "unknown_unique" DETAIL value contains "users_email_unique"'];
        $this->assertSame(500, app(Handler::class)->render($request, new QueryException('pgsql', 'sql', [], $previous))->getStatusCode());
        $previous->errorInfo = ['23503', 7, 'users_email_unique'];
        $this->assertSame(500, app(Handler::class)->render($request, new QueryException('pgsql', 'sql', [], $previous))->getStatusCode());
    }

    public function test_uniqueness_conflict_in_an_html_form_preserves_input_but_never_passwords(): void
    {
        Route::middleware('web')->post('/__test/duplicate-email', function () {
            $previous = new PDOException('duplicate key');
            $previous->errorInfo = ['23505', 7, 'duplicate key value violates unique constraint "users_email_normalized_unique"'];
            throw new QueryException('pgsql', 'insert into users values (?)', ['private-value'], $previous);
        });
        $this->from('/admin/users/create')->post('/__test/duplicate-email', [
            'name' => 'Nama dipertahankan', 'email' => 'duplicate@example.test',
            'password' => 'secret-password', 'password_confirmation' => 'secret-password',
        ])->assertRedirect('/admin/users/create')->assertSessionHasErrors('email')
            ->assertSessionHasInput('name', 'Nama dipertahankan')->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
    }
}
