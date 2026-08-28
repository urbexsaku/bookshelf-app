<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function __construct(
        private RankingService $rankingService
    ) {}

    /**
     * ランキング画面を表示する。
     *
     * @return View ランキング画面のビュー
     */
    public function index(): View
    {
        $rankedBooks = $this->rankingService->getRanking();

        return view('ranking.index', compact('rankedBooks'));
    }
}
