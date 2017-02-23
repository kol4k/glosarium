<?php

/**
 * Glosarium adalah aplikasi berbasis web yang menyediakan berbagai kata glosarium,
 * kamus nasional dan kamus bahasa daerah.
 *
 * @author Yugo <dedy.yugo.purwanto@gmail.com>
 * @copyright Glosarium - 2017
 */

namespace App\Http\Controllers\Glosarium;

use App\Glosarium\Word;
use App\Http\Controllers\Controller;
use App\Http\Requests\Glosarium\WordRequest;
use App\Libraries\Image;
use App\Libraries\Wikipedia;
use App\Mail\Glosarium\CreateMail;
use Auth;
use Cache;
use Carbon\Carbon;
use Mail;

/**
 * Manage glosarium words
 */
class WordController extends Controller
{
    private $cacheTime;

    public function __construct()
    {
        $this->cacheTime = Carbon::now()->addDays(30);

        view()->share([
            'js' => [
                'route' => \Route::currentRouteName(),
                'api'   => [
                    'wordIndex'     => route('api.word.index'),
                    'categoryIndex' => route('api.category.index'),
                    'allCategory'   => route('api.category.all'),
                ],
            ],
        ]);
    }

    /**
     * Show all words
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $totalWord = Cache::get('glosarium.total', function () {
            return Word::whereIsPublished(true)->count();
        });

        return view('glosariums.words.index', compact('totalWord'))
            ->withTitle(trans('glosarium.index'));
    }

    /**
     * Show single and detailed word
     *
     * @param  string                     $category
     * @param  string                     $slug
     * @return Illuminate\Http\Response
     */
    public function show($category, $slug)
    {
        $totalWord = Word::whereIsPublished(true)->count();

        $word = Word::whereSlug($slug)
            ->with('category', 'descriptions', 'user')
            ->firstOrFail();

        // wikipedia page
        $wikipedia  = new Wikipedia;
        $wikipedias = $wikipedia->openSearch($word->locale);
        if (empty($wikipedias)) {
            $wikipedias = $wikipedia->openSearch($word->origin);
        }

        // generate image
        $image = new Image;
        $image->addText($word->origin, 50, 400, 150)
            ->addText($word->locale, 40, 400, 250)
            ->render(sprintf('images/glosariums/%s', $word->category->slug), $word->slug);

        $imagePath = $image->path();

        // short link
        $hash = base_convert($word->id, 20, 36);
        $link = \App\Link::firstOrCreate([
            'hash' => $hash,
            'type' => 'glosarium',
            'url'  => route('glosarium.word.show', [$word->category->slug, $word->slug]),
        ]);

        return view('glosariums.words.show', compact('totalWord', 'word', 'wikipedias', 'imagePath', 'link'))
            ->withTitle(trans('glosarium.show', [
                'origin' => $word->origin,
                'locale' => $word->locale,
            ]));
    }

    /**
     * Find similar word
     *
     * @return string JSON
     */
    public function sameWord()
    {
        // find similar category
        $words = Word::whereOrigin(request('origin'))
            ->with('category')
            ->get();

        return response()->json([
            'words' => $words,
        ]);

    }

    /**
     * Count word and get total
     *
     * @return string JSON
     */
    public function total()
    {
        abort_if(!request()->ajax(), 404, 'Halaman tidak ditemukan.');

        $cacheTime = \Carbon\Carbon::now()->addDays(7);
        $total     = Cache::remember('glosarium.total', $cacheTime, function () {
            return \App\Glosarium\Word::count();
        });

        return response()->json([
            'isSuccess' => true,
            'total'     => number_format($total, 0, ',', '.'),
        ]);
    }

    /**
     * Show create form
     *
     * @return Illuminate\Http\Response
     */
    public function create()
    {
        // create image
        $image = new Image;

        $image->addText(trans('glosarium.create'), 40, 400, 200)
            ->render('images/pages', 'create-glossary');

        $imagePath = $image->path();

        return view('glosariums.words.create', compact('imagePath'))
            ->withTitle(trans('glosarium.create'));
    }

    /**
     * Create and store new glossary
     *
     * @param  WordRequest $request
     * @return string      JSON
     */
    public function store(WordRequest $request)
    {
        try {
            $glosarium = Word::create([
                'user_id'      => Auth::id(),
                'category_id'  => $request->category,
                'origin'       => $request->origin,
                'locale'       => $request->locale,
                'lang'         => 'en',
                'is_published' => Auth::user()->type == 'admin',
                'is_standard'  => false,
                'retry_count'  => 0,
            ]);

            // send email proposal
            Mail::to(config('mail.from.address'))->send(new CreateMail($glosarium));

            return response()->json([
                'isSuccess' => true,
                'glosarium' => $glosarium,
                'alerts'    => [
                    'type'    => 'success',
                    'title'   => trans('glosarium.success'),
                    'message' => trans('glosarium.msg.created'),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'isSuccess' => false,
                'message'   => $e->getMessage(),
            ]);

            abort(500, $e->getMessage());
        }
    }

    /**
     * Get latest words
     *
     * @return string JSON
     */
    public function latest()
    {
        abort_if(!request()->ajax(), 404, 'Halaman tidak ditemukan.');

        $words = Word::orderBy('created_at', 'DESC')
            ->with('category')
            ->whereIsPublished(true)
            ->limit(20)
            ->get();

        return response()->json([
            'words' => $words,
        ]);
    }
}
