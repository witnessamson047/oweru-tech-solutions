@extends('layouts.public')

@section('title', 'Payment Status - Oweru Tech Solutions')

@section('content')
<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-center">
            @if($payment->status === 'completed')
                {{-- Success --}}
                <div class="p-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                    <p class="text-gray-500 text-sm mb-6">Thank you for your payment. A confirmation will be sent to your email.</p>

                    <div class="bg-gray-50 rounded-lg p-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Reference</span>
                            <span class="font-mono text-gray-900">{{ $payment->merchant_reference }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Amount</span>
                            <span class="font-bold text-gray-900">TZS {{ number_format($payment->amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Status</span>
                            <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> Completed
                            </span>
                        </div>
                        @if($payment->payment_method)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Method</span>
                            <span class="text-gray-900">{{ $payment->payment_method }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Date</span>
                            <span class="text-gray-900">{{ $payment->paid_at?->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    @if($payment->invoice && $payment->invoice->is_fully_paid && $payment->invoice->receipt_path)
                        <a href="{{ route('invoice.receipt', [$payment->invoice, $payment->invoice->receipt_token]) }}" class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition">
                            ⬇ Download Receipt
                        </a>
                    @elseif($payment->invoice)
                        <p class="mt-4 text-xs text-gray-500">
                            {{ $payment->invoice->is_fully_paid
                                ? 'Your official receipt is being generated and will be emailed to you.'
                                : 'Deposit received! Balance: TZS ' . number_format($payment->invoice->balance_due, 0) . ' — payable on completion.' }}
                        </p>
                    @endif

                    <a href="{{ route('home') }}" class="mt-6 inline-block bg-yellow-500 hover:bg-yellow-500 text-black font-bold py-2.5 px-6 rounded-lg text-sm transition">
                        Back to Home →
                    </a>
                </div>
            @elseif($payment->status === 'failed')
                {{-- Failed --}}
                <div class="p-8">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
                    <p class="text-gray-500 text-sm mb-6">Your payment could not be processed. Please try again.</p>

                    <div class="bg-gray-50 rounded-lg p-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Reference</span>
                            <span class="font-mono text-gray-900">{{ $payment->merchant_reference }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Amount</span>
                            <span class="font-bold text-gray-900">TZS {{ number_format($payment->amount) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3 justify-center">
                        @php
                            $retryType = match (class_basename($payment->paymentable_type)) {
                                'ServicePackage' => 'package',
                                'CarePlan' => 'care-plan',
                                'Invoice' => ($payment->kind === App\Models\Payment::KIND_FULL ? 'invoice-full' : 'invoice-deposit'),
                                default => 'scan',
                            };
                        @endphp
                        <a href="{{ route('payment.checkout', ['type' => $retryType, 'id' => $payment->paymentable_id]) }}"
                            class="bg-yellow-500 hover:bg-yellow-500 text-black font-bold py-2.5 px-6 rounded-lg text-sm transition">
                            Try Again
                        </a>
                        <a href="{{ route('home') }}" class="border border-gray-300 text-gray-700 font-medium py-2.5 px-6 rounded-lg text-sm hover:bg-gray-50 transition">
                            Back to Home
                        </a>
                    </div>
                </div>
            @else
                {{-- Pending --}}
                <div class="p-8">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Pending</h1>
                    <p class="text-gray-500 text-sm mb-4">Your payment is being processed. This may take a moment.</p>
                    <p class="text-xs text-gray-400">Reference: {{ $payment->merchant_reference }}</p>

                    <p class="mt-6 text-sm text-gray-500">You can close this page and check back later, or wait for the confirmation.</p>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
