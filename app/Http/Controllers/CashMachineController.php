<?php

namespace App\Http\Controllers;

use App\Services\CashMachine\Exceptions\ServiceException;

use App\Http\Requests\{
    AccountDepositRequest,
    AccountWithdrawRequest,
    PaymentRequest,
    DepositStateRequest,
    ChargeStateRequest
};

use App\Services\CashMachine\{
    AccountDepositService,
    AccountChargeService,
    UserAccountService
};

class CashMachineController extends Controller
{
    private $accountService;
    private $depositService;
    private $chargeService;

    public function __construct(AccountDepositService $depositService,
                                AccountChargeService $chargeService,
                                UserAccountService $accountService)
    {
        $this->depositService = $depositService;
        $this->chargeService = $chargeService;
        $this->accountService = $accountService;
    }

    public function deposit(AccountDepositRequest $request, $accountId)
    {
        $deposit = $this->depositService->add($accountId,
            $request->get('source'),
            $request->get('operation_date'),
            $request->get('liquidation_date'),
            $request->get('description'),
            (float) $request->get('amount'));

        if (!empty($deposit)) {
            return response()->json([
                'success' => true,
                'tracking_id' => $deposit->tracking_id,
                'id' => $deposit->id
            ]);
        }
    }

    public function withdraw(AccountWithdrawRequest $request, $accountId)
    {
        return response()->json($this
            ->accountService->withdraw(
                auth()->user()->id,
                $accountId,
                (float) $request->get('amount'),
                $request->get('description', null)));
    }

    public function pay(PaymentRequest $request)
    {
        $payment = $this->chargeService->addPayment($request->get('account_id'),
            (float) $request->get('amount'),
            $request->get('description', null));

        return response()->json([
            'status' => "success",
            'tracking_id' => $payment->id
        ]);
    }

    public function changeDepositState(DepositStateRequest $request, $state)
    {
        $this->validateTransactionState($state);

        $this->depositService->setState($request->get('id'), $state);

        return response()->json([
            'status' => "success"
        ]);
    }

    public function changeChargeState(ChargeStateRequest $request, $state)
    {
        $this->validateTransactionState($state);

        $this->chargeService->setState($request->get('id'), $state);

        return response()->json([
            'status' => "success"
        ]);
    }

    private function validateTransactionState(string $state): bool
    {
        if (!in_array($state, ['approved', 'rejected', 'cancel'])) {
            throw new ServiceException("Invalid deposit state.", 400);
        }

        return true;
    }
}
