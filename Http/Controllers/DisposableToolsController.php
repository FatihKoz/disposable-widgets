<?php

namespace Modules\DisposableTools\Http\Controllers;

use App\Contracts\Controller;

class DisposableToolsController extends Controller
{
  // Admin Page
  public function admin() { return view('DisposableTools::admin'); }

}
