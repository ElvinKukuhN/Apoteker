<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Asset;
use App\Stock;
use Exception;
use App\Transaction;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\StoreTransactionRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Requests\MassDestroyTransactionRequest;

/**
 * Class TransactionsController
 * @package App\Http\Controllers\Admin
 */
class TransactionsDisposableController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        abort_if(Gate::denies('transaction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transactions = Transaction::whereNotNull('disposable_id')->get();

        return view('admin.transactionsDisposable.index', compact('transactions'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        abort_if(Gate::denies('transaction_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assets = Asset::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.transactions.create', compact('assets', 'users'));
    }

    /**
     * @param StoreTransactionRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->all());

        return redirect()->route('admin.transactions.index');

    }

    /**
     * @param Transaction $transaction
     * @return Factory|View
     */
    public function edit(Transaction $transaction)
    {
        abort_if(Gate::denies('transaction_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assets = Asset::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $transaction->load('asset', 'team', 'user');

        return view('admin.transactions.edit', compact('assets', 'users', 'transaction'));
    }

    /**
     * @param UpdateTransactionRequest $request
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->all());

        return redirect()->route('admin.transactions.index');

    }

    /**
     * @param Transaction $transaction
     * @return Factory|View
     */
    public function show(Transaction $transaction)
    {
        abort_if(Gate::denies('transaction_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaction->load('asset', 'team', 'user');

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * @param Transaction $transaction
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Transaction $transaction)
    {
        abort_if(Gate::denies('transaction_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaction->delete();

        return back();

    }

    /**
     * @param MassDestroyTransactionRequest $request
     * @return ResponseFactory|\Illuminate\Http\Response
     */
    public function massDestroy(MassDestroyTransactionRequest $request)
    {
        Transaction::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);

    }

    /**
     * @param Stock $stock
     * @return RedirectResponse
     */
    public function storeStock(Stock $stock)
    {
        $action = request()->input('action', 'add') == 'add' ? 'add' : 'remove';
        $stockAmount = request()->input('stock', 1);
        $sign = $action == 'add' ? '+' : '-';

        if ($stockAmount < 1) {
            return redirect()->route('admin.stocks.index')->with([
                'error' => 'No item was added/removed. Amount must be greater than 1.',
            ]);
        }

        Transaction::create([
            'stock' => $sign . $stockAmount,
            'asset_id' => $stock->asset->id,
            'team_id' => $stock->team->id,
            'user_id' => auth()->user()->id,
        ]);

        if ($action == 'add') {
            $stock->increment('current_stock', $stockAmount);
            $status = $stockAmount . ' item(-s) was added to stock.';
        }

        if ($action == 'remove') {
            if ($stock->current_stock - $stockAmount < 0) {
                return redirect()->route('admin.stocks.index')->with([
                    'error' => 'Not enough items in stock.',
                ]);
            }

            $stock->decrement('current_stock', $stockAmount);
            $status = $stockAmount . ' item(-s) was removed from stock.';
        }

        return redirect()->route('admin.stocks.index')->with([
            'status' => $status,
        ]);
    }

    public function storeStockDisposable(Stock $stock)
    {
        $action = request()->input('action', 'add') == 'add' ? 'add' : 'remove';
        $stockAmount = request()->input('stock', 1);
        $sign = $action == 'add' ? '+' : '-';

        if ($stockAmount < 1) {
            return redirect()->route('admin.stocks.index')->with([
                'error' => 'No item was added/removed. Amount must be greater than 1.',
            ]);
        }

        Transaction::create([
            'stock' => $sign . $stockAmount,
            'disposable_id' => $stock->disposable->id,
            'team_id' => $stock->team->id,
            'user_id' => auth()->user()->id,
        ]);

        if ($action == 'add') {
            $stock->increment('current_stock', $stockAmount);
            $status = $stockAmount . ' item(-s) was added to stock.';
        }

        if ($action == 'remove') {
            if ($stock->current_stock - $stockAmount < 0) {
                return redirect()->route('admin.stocks.index')->with([
                    'error' => 'Not enough items in stock.',
                ]);
            }

            $stock->decrement('current_stock', $stockAmount);
            $status = $stockAmount . ' item(-s) was removed from stock.';
        }

        return redirect()->route('admin.stocksDisposable.index')->with([
            'status' => $status,
        ]);
    }

    public function statusTransaction(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return redirect()->route('admin.transactions.index');
        }

        $newStatus = $transaction->is_transaction == 1 ? 0 : 1;

        $updateTransaction = $transaction->update([
            'is_transaction' => $newStatus
        ]);

        if ($updateTransaction) {
            return redirect()->route('admin.transactions.index');
        } else {
            return redirect()->route('admin.transactions.index');
        }
    }


}
