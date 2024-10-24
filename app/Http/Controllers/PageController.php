<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;


class PageController extends Controller
{
    public function getOneQuote($id) {
        $quote = Quote::all()->find(rand(1,100));
        $author = explode(' ', $quote->author);
        dd($quote);
    }
}
