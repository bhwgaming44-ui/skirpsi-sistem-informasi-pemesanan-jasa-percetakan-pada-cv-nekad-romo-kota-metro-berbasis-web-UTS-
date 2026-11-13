<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanRedirectHeaders
{
    /**
     * Header yang nilainya harus satu baris (tidak boleh mengandung CR/LF).
     *
     * @var array<int, string>
     */
    private const SINGLE_LINE_HEADERS = ['Location', 'Content-Disposition', 'Refresh'];

    /**
     * Membersihkan header satu baris dari karakter baris baru sebelum dikirim.
     *
     * Nilai environment atau URL yang tidak sengaja berisi newline membuat
     * PHP melempar "Header may not contain more than a single header",
     * sehingga redirect apa pun berubah menjadi error 500.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::SINGLE_LINE_HEADERS as $name) {
            if (! $response->headers->has($name)) {
                continue;
            }

            $value = (string) $response->headers->get($name);
            $clean = trim(preg_replace('/[\r\n]+/', '', $value) ?? '');

            $response->headers->set($name, $clean);
        }

        return $response;
    }
}
