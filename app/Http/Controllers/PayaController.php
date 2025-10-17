<?php

namespace App\Http\Controllers;

use App\DTO\PayaRequestDTO;
use App\Models\PayaRequest;
use App\Services\PayaRequestService;
use Illuminate\Http\Request;

class PayaController extends Controller
{
    protected $payaService;

    /**
     * PayaController constructor.
     *
     * @param PayaRequestService $payaService
     */
    public function __construct(PayaRequestService $payaService)
    {
        $this->payaService = $payaService;
    }

    /**
     * نمایش لیست درخواست‌های پایا (مرتب‌شده بر اساس زمان ثبت)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $requests = PayaRequest::select('uuid', 'price', 'status', 'from_account_id', 'to_sheba_number as ToShebaNumber', 'created_at')
            ->with(['fromAccount:id,sheba_number'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->uuid,
                    'price' => (float) $request->price,
                    'status' => $request->status->value,
                    'fromShebaNumber' => $request->fromAccount->sheba_number,
                    'ToShebaNumber' => $request->ToShebaNumber,
                    'createdAt' => $request->created_at,
                ];
            });

        return response()->json(['requests' => $requests]);
    }

    /**
     * ثبت درخواست جدید پایا
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:1',
            'fromShebaNumber' => ['required', 'regex:/^IR[0-9]{24}$/'],
            'ToShebaNumber' => ['required', 'regex:/^IR[0-9]{24}$/'],
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $dto = PayaRequestDTO::fromArray($validated);
            $payaRequest = $this->payaService->createRequest($dto);


            return response()->json([
                'message' => 'Request is saved successfully and is in pending status',
                'request' => [
                    'id' => $payaRequest->uuid,
                    'price' => (float) $payaRequest->price,
                    'status' => $payaRequest->status->value,
                    'fromShebaNumber' => $payaRequest->fromAccount->sheba_number,
                    'ToShebaNumber' => $payaRequest->to_sheba_number,
                    'createdAt' => $payaRequest->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => 'ERROR_' . $e->getCode()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * تایید یا لغو درخواست پایا
     *
     * @param Request $request
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $uuid)
    {
        $allowedStatuses = collect(PayaRequest::TARGET_STATUSES)->pluck('value')->implode(',');
        $validated = $request->validate([
            'status' => 'required|in:'. $allowedStatuses,
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $payaRequest = PayaRequest::where('uuid', $uuid)->firstOrFail();
            $payaRequest = $this->payaService->updateRequestStatus($payaRequest, $validated['status'], $validated['note']);

            return response()->json([
                'message' => $validated['status'] === 'confirmed' ? 'Request is Confirmed!' : 'Request is Canceled!',
                'request' => [
                    'id' => $payaRequest->uuid,
                    'price' => (float) $payaRequest->price,
                    'status' => $payaRequest->status->value,
                    'fromShebaNumber' => $payaRequest->fromAccount->sheba_number,
                    'ToShebaNumber' => $payaRequest->to_sheba_number,
                    'createdAt' => $payaRequest->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => 'ERROR_' . $e->getCode()
            ], $e->getCode() ?: 400);
        }
    }
}
