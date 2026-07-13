<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentProofController extends Controller
{
    public function showForOrder(OnlineOrder $order): BinaryFileResponse
    {
        return $this->streamPaymentProof($order);
    }

    public function showForTracking(string $token): BinaryFileResponse
    {
        $order = OnlineOrder::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        return $this->streamPaymentProof($order);
    }

    private function streamPaymentProof(OnlineOrder $order): BinaryFileResponse
    {
        abort_unless($order->payment_proof_path, Response::HTTP_NOT_FOUND);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($order->payment_proof_path)) {
                $path = Storage::disk($disk)->path($order->payment_proof_path);
                $mimeType = Storage::disk($disk)->mimeType($order->payment_proof_path) ?: 'application/octet-stream';

                $response = response()->file($path, [
                    'Content-Type' => $mimeType,
                    'Pragma' => 'no-cache',
                    'X-Content-Type-Options' => 'nosniff',
                ]);

                $response->headers->set(
                    'Cache-Control',
                    'private, no-store, max-age=0, must-revalidate',
                    true
                );

                return $response;
            }
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}
