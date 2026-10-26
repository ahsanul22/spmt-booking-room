<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (QueryException $exception, Request $request) {
            if (($exception->errorInfo[0] ?? null) !== '23505') {
                return null;
            }
            // Validation can succeed simultaneously for two writes. Keep the database
            // constraint authoritative, and map only recognised uniqueness conflicts.
            $constraints = [
                'branches_name_unique' => ['name', 'Nama kantor / cabang sudah digunakan.'],
                'facilities_name_unique' => ['name', 'Nama fasilitas sudah digunakan.'],
                'floors_floor_number_unique' => ['floor_number', 'Nomor lantai sudah digunakan.'],
                'rooms_code_unique' => ['code', 'Kode ruangan sudah digunakan.'],
                'users_email_unique' => ['email', 'Email sudah digunakan oleh akun lain.'],
                'users_email_normalized_unique' => ['email', 'Email sudah digunakan oleh akun lain.'],
            ];
            preg_match('/unique constraint "([^"]+)"/', $exception->errorInfo[2] ?? '', $matches);
            if (! isset($constraints[$matches[1] ?? ''])) {
                return null;
            }
            [$field, $message] = $constraints[$matches[1]];

            return $this->convertValidationExceptionToResponse(
                ValidationException::withMessages([$field => $message]), $request,
            );
        });
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
