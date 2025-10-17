<?php

namespace App\Services;

use App\DTO\PayaRequestDTO;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\PayaRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayaRequestService
{
    public function createRequest(PayaRequestDTO $data)
    {
        return DB::transaction(function () use ($data) {

            $data->validate();
            $account = $this->findAccountBySheba($data->fromShebaNumber);
            if (!$account) {
                throw new \Exception('Invalid source Sheba number', 400);
            }

            if ($account->balance < $data->price) {
                throw new \Exception('Insufficient balance', 400);
            }

            $shebaRequest = PayaRequest::create([
                'uuid' => Str::uuid(),
                'price' => $data->price,
                'from_account_id' => $account->id,
                'to_sheba_number' => $data->toShebaNumber,
                'status' => 'pending',
                'note' => $data->note,
            ]);

            $account->balance -= $data->price;
            $account->reserved = $data->price;
            $account->save();

            AccountTransaction::create([
                'account_id' => $account->id,
                'type' => TransactionType::DEPOSIT,
                'amount' => $data->price,
                'description' => 'Deposit for Sheba request #' . $shebaRequest->id,
            ]);

            return $shebaRequest;
        });
    }

    public function updateRequestStatus(PayaRequest $payaRequest, string $status, ?string $note)
    {
        return DB::transaction(function () use ($payaRequest, $status, $note) {
            if (!in_array($payaRequest->status, PayaRequest::CHANGEABLE_STATUSES)) {
                throw new \Exception('Request status can not be changed.', 400);
            }
            $payaRequest->status = $status;
            $payaRequest->note = $note ?? $payaRequest->note;
            $payaRequest->save();

            if ($status === 'canceled') {
                $this->refundPayaRequest($payaRequest);
            } elseif ($status === 'confirmed') {
                $toAccount = $this->findAccountBySheba($payaRequest->to_sheba_number);
                if ($toAccount) {
                    $toAccount->balance += $payaRequest->price;
                    $toAccount->save();
                    AccountTransaction::create([
                        'account_id' => $toAccount->id,
                        'type' => TransactionType::DEPOSIT,
                        'amount' => $payaRequest->price,
                        'description' => 'Withdraw for Paya request #' . $payaRequest->uuid,
                    ]);
                } else {
                    $this->refundPayaRequest($payaRequest);
                    throw new \Exception('Invalid destination sheba number', 400);
                }
            }

            return $payaRequest;
        });
    }

    private function refundPayaRequest(PayaRequest $payaRequest) : void
    {
        $account = $payaRequest->account;
        $account->reserved -= $payaRequest->price;
        $account->balance += $payaRequest->price;
        $account->save();

        AccountTransaction::create([
            'account_id' => $account->id,
            'type' => TransactionType::REFUND,
            'amount' => $payaRequest->price,
            'description' => 'Refund for Paya request #' . $payaRequest->uuid,
        ]);
    }

    protected function findAccountBySheba(string $sheba): ?Account
    {
        return Account::where('sheba_number', $sheba)->first();
    }
}
