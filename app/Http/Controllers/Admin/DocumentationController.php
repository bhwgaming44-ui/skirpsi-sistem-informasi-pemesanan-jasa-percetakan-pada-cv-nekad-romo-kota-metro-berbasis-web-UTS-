<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DocumentationController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.documentation.index');
    }
}
