<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAccountRequest;
use App\Services\CashMachine\UserAccountService;


class UserAccountsController extends Controller
{
    private $service;

    public function __construct(UserAccountService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json($this->service->getAccounts(auth()->user()->id));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserAccountRequest $request)
    {
        return $this->service->add([
            'user_id' => auth()->user()->id,
            'account_type' => $request->get('account_type'),
            'alias' => $request->get('alias'),
            'credit_line' => $request->get('credit_line', null)
        ])->only(['id', 'account_type', 'alias', 'created_date']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($this->service->getAccount(auth()->user()->id, $id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserAccountRequest $request, $id)
    {
        return $this->service->update(auth()->user()->id, $id, [
            'alias' => $request->get('alias', null),
            'account_type' => $request->get('account_type', null)
        ]);
    }
}
