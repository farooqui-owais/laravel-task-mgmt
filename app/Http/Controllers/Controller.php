<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * Base controller.
 *
 * NOTE: We intentionally do NOT use the AuthorizesRequests trait here.
 * In Laravel 11, authorization in controllers is handled via Gate::authorize()
 * (the facade approach), which is cleaner and avoids the trait dependency.
 * See TaskController and UserController for usage.
 */
abstract class Controller {}
