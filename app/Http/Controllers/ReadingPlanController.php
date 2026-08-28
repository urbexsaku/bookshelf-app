<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧画面を表示する。
     *
     * @param  Request  $request  計画状態を含むリクエスト
     * @return View 読書計画一覧画面のビュー
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');

        $currentStatus = $status;

        $statusEnum = $status
            ? ReadingPlanStatus::tryFrom($status)
            : null;

        $readingPlans = auth()->user()
            ->readingPlans()
            ->with('book')
            ->statusFilter($statusEnum)
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画作成画面を表示する。
     *
     * @return View 読書計画作成画面のビュー
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する。
     *
     * @param  ReadingPlanRequest  $request  登録する読書計画情報を含むリクエスト
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $request->book_id,
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画編集画面を表示する。
     *
     * @param  ReadingPlan  $readingPlan  編集する読書計画
     * @return View 読書計画編集画面のビュー
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画を更新する。
     *
     * @param  ReadingPlanRequest  $request  更新する読書計画情報を含むリクエスト
     * @param  ReadingPlan  $readingPlan  更新する読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function update(ReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->target_date,
            'status' => $readingPlan->status === ReadingPlanStatus::Expired
                ? ReadingPlanStatus::InProgress
                : $readingPlan->status,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する。
     *
     * @param  ReadingPlan  $readingPlan  削除する読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画を完了する。
     *
     * @param  ReadingPlan  $readingPlan  完了する読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => today(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を完了しました。');
    }
}
