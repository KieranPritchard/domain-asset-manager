<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    // renders the view
    public function index()
    {
        return view("upload");
    }
}
